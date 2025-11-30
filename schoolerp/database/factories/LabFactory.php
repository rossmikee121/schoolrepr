<?php

namespace Database\Factories;

use App\Models\Lab\Lab;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabFactory extends Factory
{
    protected $model = Lab::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Lab',
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{2}'),
            'capacity' => $this->faker->numberBetween(20, 50),
            'location' => 'Room ' . $this->faker->numberBetween(101, 999),
            'equipment' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}