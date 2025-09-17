<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = StampCorrectionRequest::with(['attendance.user'])
            ->orderBy('created_at', 'desc');

        if (auth()->user()->isAdmin()) {
            // 管理者 → 全ユーザー
            if ($status === 'approved') {
                $query->where('status', 'approved');
            } else {
                $query->where('status', 'pending');
            }
        } else {
            // 一般ユーザー → 自分のみ
            $query->where('user_id', auth()->id());
        }

        $requests = $query->paginate(10);

        return view('correction_request.index', compact('requests', 'status'));
    }

    public function approve($id)
    {
        $request = StampCorrectionRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return redirect()->back()->with('error', 'この申請は既に処理済みです。');
        }

        $attendance = $request->attendance;

        // 出退勤更新
        $attendance->clock_in_at = $request->clock_in_at
            ? Carbon::parse($attendance->date->toDateString() . ' ' . $request->clock_in_at)
            : null;
        $attendance->clock_out_at = $request->clock_out_at
            ? Carbon::parse($attendance->date->toDateString() . ' ' . $request->clock_out_at)
            : null;
        $attendance->save();

        // 休憩更新
        if ($request->breaks) {
            foreach ($request->breaks as $index => $br) {
                $break = $attendance->breakTimes[$index] ?? $attendance->breakTimes()->create([]);
                $break->start_time = $br['start_time']
                    ? Carbon::parse($attendance->date->toDateString() . ' ' . $br['start_time'])
                    : null;
                $break->end_time = $br['end_time']
                    ? Carbon::parse($attendance->date->toDateString() . ' ' . $br['end_time'])
                    : null;
                $break->save();
            }
        }

        // 承認フラグ更新
        $request->status = 'approved';
        $request->approved_at = now();
        $request->approved_by = auth()->id();
        $request->save();

        return redirect()->route('correction_request.list')->with('status', '修正申請を承認しました');
    }
}

