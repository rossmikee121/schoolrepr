<?php

namespace Database\Factories;

use App\Models\Result\Subject;
use App\Models\Academic\Program;
use App\Models\Academic\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => $this->faker->randomElement(['Mathematics', 'Physics', 'Chemistry', 'Computer Science']),
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'credits' => $this->faker->numberBetween(1, 4),
            'type' => $this->faker->randomElement(['theory', 'practical', 'both']),
            'max_marks' => 100,
            'passing_marks' => 40,
            'is_active' => true,
        ];
    }
}