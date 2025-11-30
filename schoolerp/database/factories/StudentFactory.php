<?php

namespace Database\Factories;

use App\Models\User\Student;
use App\Models\User;
use App\Models\Academic\Program;
use App\Models\Academic\Division;
use App\Models\Academic\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admission_number' => $this->faker->unique()->numerify('ADM####'),
            'roll_number' => $this->faker->unique()->regexify('2025/[A-Z]{4}/[A-C]/\d{3}'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'date_of_birth' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'program_id' => Program::factory(),
            'academic_year' => '2025-26',
            'division_id' => Division::factory(),
            'academic_session_id' => AcademicSession::factory(),
            'admission_date' => $this->faker->date(),
            'student_status' => 'active',
        ];
    }
}