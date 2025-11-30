<?php

namespace Database\Factories;

use App\Models\Academic\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicSessionFactory extends Factory
{
    protected $model = AcademicSession::class;

    public function definition(): array
    {
        return [
            'session_name' => $this->faker->randomElement(['2024-25', '2025-26', '2026-27']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'is_current' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}