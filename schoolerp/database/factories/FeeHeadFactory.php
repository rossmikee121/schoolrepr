<?php

namespace Database\Factories;

use App\Models\Fee\FeeHead;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeHeadFactory extends Factory
{
    protected $model = FeeHead::class;

    public function definition(): array
    {
        $feeHeads = [
            ['name' => 'Tuition Fee', 'code' => 'TF'],
            ['name' => 'Practical Fee', 'code' => 'PF'],
            ['name' => 'Library Fee', 'code' => 'LF'],
            ['name' => 'Sports Fee', 'code' => 'SF'],
        ];
        
        $feeHead = $this->faker->randomElement($feeHeads);
        
        return [
            'name' => $feeHead['name'],
            'code' => $feeHead['code'] . $this->faker->unique()->randomNumber(2),
            'description' => $this->faker->sentence(),
            'is_refundable' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}