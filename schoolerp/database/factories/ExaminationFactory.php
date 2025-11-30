<?php

namespace Database\Factories;

use App\Models\Result\Examination;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExaminationFactory extends Factory
{
    protected $model = Examination::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['First Internal', 'Second Internal', 'Final Exam']),
            'code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{4}'),
            'type' => $this->faker->randomElement(['internal', 'external', 'practical']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'academic_year' => '2025-26',
            'status' => 'scheduled',
        ];
    }
}