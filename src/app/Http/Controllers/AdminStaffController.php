<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧表示
     */
    public function index()
    {
        // 一般ユーザーのみを対象にしたい場合は role カラム等で絞る
        $staffs = User::orderBy('name')->get();

        return view('admin.staff.index', compact('staffs'));
    }

    /**
     * 特定スタッフの月次勤怠一覧へリダイレクト
     */
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $ym = $request->query('ym');
        if (!$ym || !preg_match('/^\d{6}$/', $ym)) {
            $start = Carbon::today()->startOfMonth();
        } else {
            $start = Carbon::createFromFormat('Ym', $ym)->startOfMonth();
        }
        $end = $start->copy()->endOfMonth();

        $prevYm = $start->copy()->subMonth()->format('Ym');
        $nextYm = $start->copy()->addMonth()->format('Ym');

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('breakTimes')
            ->get()
            ->keyBy(fn($a) => $a->date->toDateString());

        $rows = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $attendance = $attendances->get($key);

            $rows[] = [
                'carbon'     => $day->copy(),
                'attendance' => $attendance,
            ];
        }

        // 表示ラベル（例: 2025/09）
        $displayLabel = $start->format('Y/m');

        return view('admin.staff.show', compact(
            'user', 'rows', 'displayLabel', 'prevYm', 'nextYm'
        ));
    }

    /**
     * CSV出力
     */
    public function exportCsv($id)
    {
        $user = User::findOrFail($id);
        $attendances = Attendance::where('user_id', $user->id)
            ->with('breakTimes')
            ->orderBy('date')
            ->get();

        $filename = $user->name . '_勤怠.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}"
        ];

        $callback = function() use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付','出勤','退勤','休憩','合計','備考']);
            foreach ($attendances as $att) {
                fputcsv($handle, [
                    $att->date_label,
                    $att->clock_in_hm,
                    $att->clock_out_hm,
                    $att->break_hm,
                    $att->work_hm,
                    $att->reason ?? ''
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}



