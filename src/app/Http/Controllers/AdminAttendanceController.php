<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * 管理者用 日次勤怠一覧
     */
    public function index(Request $request)
    {
        // 表示対象日
        $date = Carbon::parse(
            $request->query('date', Carbon::today()->toDateString())
        );

        // 指定日の勤怠データを全員分取得
        $attendances = Attendance::with(['user', 'breakTimes'])
            ->whereDate('date', $date->toDateString())
            ->get();

        // 前日・翌日
        $prevDate = $date->copy()->subDay()->toDateString();
        $nextDate = $date->copy()->addDay()->toDateString();

        return view('admin.index', compact('attendances', 'date', 'prevDate', 'nextDate'));
    }

    /**
     * 勤怠編集画面
     */
    public function edit($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        return view('admin.detail', compact('attendance'));
    }

    /**
     * 勤怠更新処理
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // バリデーション
        $validated = $request->validate([
            'clock_in'  => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'break_hm'  => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'], // 例: 01:30
        ]);

        // 更新処理
        $attendance->clock_in  = $validated['clock_in'];
        $attendance->clock_out = $validated['clock_out'];
        $attendance->break_hm  = $validated['break_hm'];

        // 勤務時間再計算（必要なら）
        if ($attendance->clock_in && $attendance->clock_out) {
            $in  = Carbon::parse($attendance->clock_in);
            $out = Carbon::parse($attendance->clock_out);
            $workMinutes = $out->diffInMinutes($in);

            // 休憩を引く
            if (!empty($attendance->break_hm)) {
                [$h, $m] = explode(':', $attendance->break_hm);
                $breakMinutes = $h * 60 + $m;
                $workMinutes -= $breakMinutes;
            }

            $attendance->work_hm = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
        }

        $attendance->save();

        return redirect()
            ->route('admin.attendance.index', ['date' => $attendance->date->toDateString()])
            ->with('success', '勤怠を更新しました');
    }
}




