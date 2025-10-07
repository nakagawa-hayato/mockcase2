<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\BreakCorrection;
use Carbon\Carbon;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending'); // デフォルトは承認待ち

    $query = StampCorrectionRequest::with(['attendance.user'])
        ->orderBy('created_at', 'desc');

    if (auth()->user()->isAdmin()) {
        // 管理者 → 全ユーザー
        $query->where('status', $status);
    } else {
        // 一般ユーザー → 自分の申請のみ
        $query->where('user_id', auth()->id())
              ->where('status', $status);
    }

    // ページネーションなし
    $requests = $query->get();

    return view('correction_request.index', compact('requests', 'status'));
    }

    public function show(StampCorrectionRequest $attendance_correction_request)
    {
        $correction = $attendance_correction_request->load(['attendance.user', 'attendance.breakTimes', 'breakCorrections']);
        return view('correction_request.show', compact('correction'));
    }

    public function approve(StampCorrectionRequest $attendance_correction_request)
    {
        $correction = $attendance_correction_request->load(['breakCorrections', 'attendance.breakTimes']);

        if ($correction->status !== 'pending') {
            return redirect()->back()->with('error', 'この申請は既に処理済みです。');
        }

        $attendance = $correction->attendance;

        // ===== 出退勤更新 =====
        $attendance->clock_in_at = $correction->clock_in_at
            ? Carbon::parse($correction->clock_in_at)
            : null;

        $attendance->clock_out_at = $correction->clock_out_at
            ? Carbon::parse($correction->clock_out_at)
            : null;

        $attendance->save();

        // ===== 休憩時間更新 =====
        $attendance->breakTimes()->delete();

        foreach ($correction->breakCorrections as $br) {
            $attendance->breakTimes()->create([
                'start_time' => $br->start_time ? Carbon::parse($br->start_time) : null,
                'end_time'   => $br->end_time   ? Carbon::parse($br->end_time)   : null,
            ]);
        }

        // ===== 申請承認フラグ更新 =====
        $correction->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()
            ->route('correction_request.show', $correction->id)
            ->with('status', '修正申請を承認しました');
    }
}
