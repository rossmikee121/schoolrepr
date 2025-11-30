<?php

namespace Database\Factories;

use App\Models\Fee\StudentFee;
use App\Models\Fee\FeeStructure;
use App\Models\User\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFeeFactory extends Factory
{
    protected $model = StudentFee::class;

    public function definition(): array
    {
        $totalAmount = $this->faker->numberBetween(10000, 100000);
        $paidAmount = 0;
        
        return [
            'student_id' => Student::factory(),
            'fee_structure_id' => FeeStructure::factory(),
            'total_amount' => $totalAmount,
            'discount_amount' => 0,
            'final_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $totalAmount - $paidAmount,
            'status' => 'pending',
        ];
    }
}