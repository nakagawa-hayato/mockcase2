<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakCorrection;

class BreakCorrectionFactory extends Factory
{
    protected $model = BreakCorrection::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('-3 months', 'now');
        $start->setTime(12, 15);
        $end = (clone $start)->modify('+1 hour');

        return [
            'stamp_correction_request_id' => null, // Seeder で設定
            'start_time' => $start,
            'end_time'   => $end,
        ];
    }
}
