<?php

namespace Database\Factories;

use App\Models\Fee\FeeStructure;
use App\Models\Fee\FeeHead;
use App\Models\Academic\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'academic_year' => '2025-26',
            'fee_head_id' => FeeHead::factory(),
            'amount' => $this->faker->numberBetween(10000, 100000),
            'installments' => $this->faker->randomElement([1, 2, 3, 4]),
            'is_active' => true,
        ];
    }
}