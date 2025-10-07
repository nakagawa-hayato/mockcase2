<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use App\Models\BreakCorrection;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ユーザー投入
        $this->call(UsersTableSeeder::class);

        // 勤怠データ
        User::all()->each(function ($user) {
            $daysToCreate = 60;

            for ($i = 0; $i < $daysToCreate; $i++) {
                $date = Carbon::today()->subDays($i);

                if ($date->isWeekend()) continue;

                $clockIn  = $date->copy()->setTime(9, 0, 0);
                $clockOut = $date->copy()->setTime(18, 0, 0);

                $attendance = Attendance::create([
                    'user_id'      => $user->id,
                    'date'         => $date->toDateString(),
                    'clock_in_at'  => $clockIn,
                    'clock_out_at' => $clockOut,
                ]);

                // 休憩（80%）
                if (rand(0, 100) < 80) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'start_time'    => $date->copy()->setTime(12, 0, 0),
                        'end_time'      => $date->copy()->setTime(13, 0, 0),
                    ]);

                    // 修正申請（15%）
                    if (rand(0, 100) < 15) {
                        $request = StampCorrectionRequest::create([
                            'user_id'       => $user->id,
                            'attendance_id' => $attendance->id,
                            'clock_in_at'   => $attendance->clock_in_at,
                            'clock_out_at'  => $attendance->clock_out_at,
                            'reason'        => 'テスト用の修正申請です',
                            'status'        => 'pending',
                        ]);

                        // 休憩修正（50%）
                        if (rand(0, 100) < 50) {
                            BreakCorrection::create([
                                'stamp_correction_request_id' => $request->id,
                                'start_time' => $date->copy()->setTime(12, 15, 0),
                                'end_time'   => $date->copy()->setTime(13, 15, 0),
                            ]);
                        }
                    }
                }
            }
        });
    }
}
