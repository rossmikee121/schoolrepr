<?php

namespace Database\Factories;

use App\Models\Attendance\Attendance;
use App\Models\User\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'attendance_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['present', 'absent', 'late']),
            'check_in_time' => $this->faker->time('H:i'),
            'remarks' => $this->faker->optional()->sentence(),
            'marked_by' => User::factory(),
        ];
    }
}