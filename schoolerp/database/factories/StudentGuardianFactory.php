<?php

namespace Database\Factories;

use App\Models\User\StudentGuardian;
use App\Models\User\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentGuardianFactory extends Factory
{
    protected $model = StudentGuardian::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'guardian_type' => $this->faker->randomElement(['father', 'mother', 'guardian']),
            'full_name' => $this->faker->name(),
            'occupation' => $this->faker->jobTitle(),
            'annual_income' => $this->faker->numberBetween(100000, 1000000),
            'mobile_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'relation' => $this->faker->randomElement(['Uncle', 'Aunt', 'Grandfather', 'Grandmother']),
            'address' => $this->faker->address(),
            'is_primary_contact' => false,
        ];
    }
}