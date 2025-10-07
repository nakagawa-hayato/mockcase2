<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
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
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validated();

        // 出勤・退勤更新
        $attendance->clock_in_at  = $validated['clock_in_at'] ?? null;
        $attendance->clock_out_at = $validated['clock_out_at'] ?? null;

        // 休憩時間更新
        if (isset($validated['breaks'])) {
            // 既存の breakTimes を削除
            $attendance->breakTimes()->delete();

            // 新しい breakTimes を作成
            foreach ($validated['breaks'] as $break) {
                if (!empty($break['start_time']) && !empty($break['end_time'])) {
                    $attendance->breakTimes()->create([
                        'start_time' => $break['start_time'],
                        'end_time'   => $break['end_time'],
                    ]);
                }
            }
        }

        // DB 保存
        $attendance->save();

        return redirect()
            ->route('admin.attendance.index', ['date' => $attendance->date->toDateString()])
            ->with('success', '勤怠を更新しました');
    }

}




