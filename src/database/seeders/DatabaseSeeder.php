<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ユーザー投入
        $this->call(UsersTableSeeder::class);

        // 勤怠データ
        User::all()->each(function ($user) {
            $daysToCreate = 60; // 過去60日分

            for ($i = 0; $i < $daysToCreate; $i++) {
                $date = Carbon::today()->subDays($i);

                if ($date->isWeekend()) {
                    continue;
                }

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
                    $break = BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'start_time'    => $date->copy()->setTime(12, 0, 0),
                        'end_time'      => $date->copy()->setTime(13, 0, 0),
                    ]);

                    // 修正申請（15%）
                    if (rand(0, 100) < 15) {
                        StampCorrectionRequest::create([
                            'user_id'       => $user->id,
                            'attendance_id' => $attendance->id,
                            'clock_in_at'   => $attendance->clock_in_at?->format('H:i'),
                            'clock_out_at'  => $attendance->clock_out_at?->format('H:i'),
                            'breaks'        => $attendance->breakTimes->map(function ($break) {
                                return [
                                    'start_time' => $break->start_time?->format('H:i'),
                                    'end_time'   => $break->end_time?->format('H:i'),
                                ];
                            })->toArray(),
                            'reason'        => 'テスト用の修正申請です',
                            'status'        => 'pending',
                        ]);
                    }
                }
            }
        });
    }
}
