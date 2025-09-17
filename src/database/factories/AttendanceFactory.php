<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $clockIn = $this->faker->dateTimeBetween('-3 months', 'now');
        $clockIn->setTime(9, 0);
        $clockOut = (clone $clockIn)->modify('+9 hours');

        return [
            'user_id'      => null, // Seeder で設定する
            'date'         => $clockIn->format('Y-m-d'),
            'clock_in_at'  => $clockIn,
            'clock_out_at' => $clockOut,
        ];
    }
}

