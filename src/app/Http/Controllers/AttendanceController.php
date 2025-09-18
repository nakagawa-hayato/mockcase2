<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠画面を表示（今日の勤怠を取得）
    public function create()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->with('breakTimes')
            ->first();

        $status = $attendance ? $attendance->status : 'off';

        return view('user.create', compact('attendance', 'status'));
    }

    // 出勤ボタン
    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        DB::transaction(function () use ($user, $today) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->lockForUpdate() // 競合を抑止
                ->first();

            // すでに出勤済み（出勤中 or 休憩中 or 退勤済）
            if ($attendance && $attendance->clock_in_at) {
                abort(409, '既に出勤済みです');
            }

            // 作成または更新
            if (!$attendance) {
                Attendance::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'clock_in_at' => now(),
                ]);
            } else {
                $attendance->update(['clock_in_at' => now()]);
            }
        });

        return redirect()->back()->with('status', '出勤しました');
    }

    // 休憩入
    public function breakIn(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        DB::transaction(function () use ($user, $today) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->lockForUpdate()
                ->firstOrFail();

            // 出勤中でなければ不可
            if ($attendance->status !== 'working') {
                abort(409, '現在休憩に入れません');
            }

            // 新しい休憩レコードを start_time = now() で作る
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => now(),
            ]);

            // status は動的算出のため DB 更新不要（ただし status カラムを使うなら update する）
        });

        return redirect()->back()->with('status', '休憩に入りました');
    }

    // 休憩戻
    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        DB::transaction(function () use ($user, $today) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attendance->status !== 'on_break') {
                abort(409, '現在休憩中ではありません');
            }

            // 未終了の休憩を取得して close
            $break = $attendance->breakTimes()->whereNull('end_time')->latest()->first();

            if (!$break) {
                abort(500, '休憩データが見つかりません');
            }

            $break->end_time = now();
            $break->save();
        });

        return redirect()->back()->with('status', '休憩から戻りました');
    }

    // 退勤
    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        DB::transaction(function () use ($user, $today) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->lockForUpdate()
                ->firstOrFail();

            // もし休憩中ならまず休憩を閉じる（自動的に休憩戻す）
            if ($attendance->status === 'on_break') {
                $break = $attendance->breakTimes()->whereNull('end_time')->latest()->first();
                if ($break) {
                    $break->end_time = now();
                    $break->save();
                }
            }

            // 出勤済みかつまだ退勤していないことを確認
            if (!$attendance->clock_in_at || $attendance->clock_out_at) {
                abort(409, '退勤できません（出勤していないか既に退勤済みです）');
            }

            // 退勤時刻を入れる
            $attendance->clock_out_at = now();
            $attendance->save();
        });

        $request->session()->flash('afterClockOut', true);

        return redirect()->back()->with('status', '退勤しました。お疲れ様でした！');
    }

    /**
     * 勤怠一覧（月表示）
     * クエリパラメータ ym=YYYYMM を受け取る（例: ?ym=202509）
     */
    public function index(Request $request)
    {
        // ログインユーザーを取得
        $user = Auth::user();

        // ym パラメータを受け取る（6桁の数字: YYYYMM  'ym'はbladeのname）
        $ym = $request->query('ym');

        // 無ければ今日の年月を使う
        if (!$ym || !preg_match('/^\d{6}$/', $ym)) {
            $start = Carbon::today()->startOfMonth();
        } else {
            // 文字列 '202509' -> Carbon インスタンス（その月の1日）
            $start = Carbon::createFromFormat('Ym', $ym)->startOfMonth();
        }

        // 月の先頭と末尾
        $end = $start->copy()->endOfMonth();

        // 前月 / 翌月 表示用キー（YYYYMM）
        $prevYm = $start->copy()->subMonth()->format('Ym');
        $nextYm = $start->copy()->addMonth()->format('Ym');

        // その月に該当する勤怠をまとめて取得（N+1 を防ぐため breakTimes を eager load）
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('breakTimes') // relation 名はモデルに合わせてください
            ->get()
            ->keyBy(fn ($a) => $a->date->toDateString()); // Y-m-d をキーに
        // 月の日ごとの配列を作る（表示用）
        $rows = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $attendance = $attendances->get($key);

            $rows[] = [
                'carbon'     => $day,
                'attendance' => $attendance,
            ];
        }

        // 表示ラベル（例: 2025年9月）
        $displayLabel = $start->format('Y年n月');

        // ビューへ渡す
        return view('user.index', compact(
            'rows',
            'displayLabel',
            'prevYm',
            'nextYm',
        ));
    }

    // 勤怠詳細
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        return view('user.detail', compact('attendance'));
    }

    public function storeCorrectionRequest(AttendanceRequest $request, $attendanceId)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($attendanceId);

        StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'clock_in_at' => $request->clock_in_at,
            'clock_out_at' => $request->clock_out_at,
            'breaks' => $request->breaks,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('attendance.show',$attendance->id)
            ->with('status', '修正申請を提出しました（承認待ち）');
    }

    public function show($id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $pendingRequest = \App\Models\StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$pendingRequest) {
            // 承認待ちが無ければ通常の編集ページへ
            return redirect()->route('attendance.edit', $attendance->id);
        }

        return view('user.request', compact('attendance', 'pendingRequest'));
    }


}
