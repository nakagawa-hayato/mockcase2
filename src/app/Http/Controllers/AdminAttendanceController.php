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

        // validated データを取得
        $validated = $request->validated();

        // 更新処理
        $attendance->clock_in_at  = $validated['clock_in_at'] ?? null;
        $attendance->clock_out_at = $validated['clock_out_at'] ?? null;
        $attendance->breaks       = $validated['breaks'] ?? null;
        $attendance->reason       = $validated['reason'] ?? null;

        // 勤務時間再計算（必要なら）
        if ($attendance->clock_in_at && $attendance->clock_out_at) {
            $in  = Carbon::createFromFormat('H:i', $attendance->clock_in_at);
            $out = Carbon::createFromFormat('H:i', $attendance->clock_out_at);
            $workMinutes = $out->diffInMinutes($in);

            // 休憩時間を引く
            if (!empty($attendance->breaks)) {
                foreach ($attendance->breaks as $break) {
                    if (!empty($break['start_time']) && !empty($break['end_time'])) {
                        $bStart = Carbon::createFromFormat('H:i', $break['start_time']);
                        $bEnd   = Carbon::createFromFormat('H:i', $break['end_time']);
                        $workMinutes -= $bEnd->diffInMinutes($bStart);
                    }
                }
            }

            $attendance->work_hm = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
        }

        $attendance->save();

        return redirect()
            ->route('admin.attendance.index', ['date' => $attendance->date->toDateString()])
            ->with('success', '勤怠を更新しました');
    }
}




