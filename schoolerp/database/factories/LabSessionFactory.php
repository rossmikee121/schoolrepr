<?php

namespace Database\Factories;

use App\Models\Lab\LabSession;
use App\Models\Lab\Lab;
use App\Models\Academic\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabSessionFactory extends Factory
{
    protected $model = LabSession::class;

    public function definition(): array
    {
        return [
            'lab_id' => Lab::factory(),
            'division_id' => Division::factory(),
            'subject_name' => $this->faker->randomElement(['Computer Programming', 'Physics Lab', 'Chemistry Lab']),
            'batch_number' => $this->faker->numberBetween(1, 5),
            'max_students' => $this->faker->numberBetween(20, 30),
            'session_date' => $this->faker->date(),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'status' => 'scheduled',
        ];
    }
}