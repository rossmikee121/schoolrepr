<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeStructure;
use App\Models\Fee\StudentScholarship;

class FeeCalculationService
{
    public static function calculateStudentFee($studentId, $feeStructureId)
    {
        $structure = FeeStructure::find($feeStructureId);
        $scholarships = StudentScholarship::where('student_id', $studentId)
            ->where('approval_status', 'approved')
            ->get();
        
        $totalAmount = $structure->total_amount;
        
        foreach($scholarships as $scholarship) {
            $totalAmount -= $scholarship->discount_amount;
        }
        
        return max(0, $totalAmount); // Never negative
    }
    
    public static function calculateInstallments($feeStructureId, $totalAmount)
    {
        $installments = DB::table('installments')
            ->where('fee_structure_id', $feeStructureId)
            ->orderBy('installment_number')
            ->get();
        
        $result = [];
        foreach($installments as $installment) {
            $result[] = [
                'installment_number' => $installment->installment_number,
                'due_date' => $installment->due_date,
                'amount' => $installment->amount,
                'status' => 'pending'
            ];
        }
        
        return $result;
    }
}
