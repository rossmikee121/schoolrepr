<?php

namespace App\Services;

use App\Models\User\Student;
use App\Models\Fee\FeeStructure;

class FeeCalculationService
{
    public static function calculateFeeWithScholarship(Student $student, FeeStructure $feeStructure): array
    {
        $totalAmount = $feeStructure->amount;
        $discountAmount = 0;

        // Calculate scholarship discount
        $scholarships = $student->scholarships()
            ->where('academic_year', $student->academic_year)
            ->where('is_active', true)
            ->get();

        foreach ($scholarships as $scholarship) {
            if ($scholarship->scholarship->type === 'percentage') {
                $discount = ($totalAmount * $scholarship->scholarship->value) / 100;
                if ($scholarship->scholarship->max_amount) {
                    $discount = min($discount, $scholarship->scholarship->max_amount);
                }
            } else {
                $discount = $scholarship->scholarship->value;
            }
            $discountAmount += $discount;
        }

        $finalAmount = $totalAmount - $discountAmount;

        return [
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => max(0, $finalAmount),
            'scholarships_applied' => $scholarships->count()
        ];
    }
}