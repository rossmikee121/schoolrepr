<?php

namespace Database\Factories;

use App\Models\Academic\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Commerce', 'Science', 'Management', 'Arts']),
            'code' => $this->faker->unique()->randomElement(['COM', 'SCI', 'MGT', 'ARTS']),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}