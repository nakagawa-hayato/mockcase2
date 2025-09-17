<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('-3 months', 'now');
        $start->setTime(12, 0);
        $end = (clone $start)->modify('+1 hour');

        return [
            'attendance_id' => null, // Seeder で設定する
            'start_time'    => $start,
            'end_time'      => $end,
        ];
    }
}
