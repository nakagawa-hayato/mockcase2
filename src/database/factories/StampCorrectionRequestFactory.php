<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    public function definition()
    {
        // ユーザーが存在しなければ作成
        $user = User::first() ?? User::factory()->create();

        // 勤怠情報を作成
        $attendance = Attendance::factory()->create([
            'user_id'     => $user->id,
            'clock_in_at' => '09:00',
            'clock_out_at'=> '18:00',
        ]);

        return [
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in_at'   => Carbon::parse($attendance->clock_in_at),
            'clock_out_at'  => Carbon::parse($attendance->clock_out_at),
            'reason'        => $this->faker->sentence(8),
            'status'        => 'pending',
            'approved_at'   => null,
            'approved_by'   => null,
        ];
    }

    /**
     * 承認待ち
     */
    public function pending()
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    /**
     * 承認済み
     */
    public function approved()
    {
        $approver = User::first() ?? User::factory()->create();

        return $this->state(fn () => [
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id,
        ]);
    }
}
