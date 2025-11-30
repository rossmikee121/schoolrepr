<?php

namespace Database\Factories;

use App\Models\Academic\Program;
use App\Models\Academic\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        $programs = [
            ['name' => 'Bachelor of Commerce', 'short_name' => 'B.Com', 'code' => 'BCOM', 'duration' => 3, 'type' => 'undergraduate'],
            ['name' => 'Bachelor of Science Computer Science', 'short_name' => 'B.Sc CS', 'code' => 'BSCS', 'duration' => 3, 'type' => 'undergraduate'],
            ['name' => 'Bachelor of Business Administration', 'short_name' => 'BBA', 'code' => 'BBA', 'duration' => 3, 'type' => 'undergraduate'],
            ['name' => 'Master of Business Administration', 'short_name' => 'MBA', 'code' => 'MBA', 'duration' => 2, 'type' => 'postgraduate'],
        ];
        
        $program = $this->faker->randomElement($programs);
        
        return [
            'department_id' => Department::factory(),
            'name' => $program['name'],
            'short_name' => $program['short_name'],
            'code' => $program['code'] . $this->faker->unique()->randomNumber(3), // Make code unique
            'duration_years' => $program['duration'],
            'total_semesters' => $program['duration'] * 2,
            'program_type' => $program['type'],
            'is_active' => true,
        ];
    }
}