<?php

namespace Database\Factories;

use App\Models\Academic\Division;
use App\Models\Academic\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'division_name' => $this->faker->randomElement(['A', 'B', 'C']),
            'max_students' => 60,
            'is_active' => true,
        ];
    }
}