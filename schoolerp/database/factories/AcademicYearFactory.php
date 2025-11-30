<?php

namespace Database\Factories;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $years = [
            ['number' => 1, 'name' => 'FY', 'sem_start' => 1, 'sem_end' => 2],
            ['number' => 2, 'name' => 'SY', 'sem_start' => 3, 'sem_end' => 4],
            ['number' => 3, 'name' => 'TY', 'sem_start' => 5, 'sem_end' => 6],
        ];
        
        $year = $this->faker->randomElement($years);
        
        return [
            'program_id' => Program::factory(),
            'year_number' => $year['number'],
            'year_name' => $year['name'],
            'semester_start' => $year['sem_start'],
            'semester_end' => $year['sem_end'],
            'is_active' => true,
        ];
    }
}