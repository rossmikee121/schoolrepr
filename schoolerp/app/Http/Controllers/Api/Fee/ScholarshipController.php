<?php

namespace App\Http\Controllers\Api\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\Scholarship;
use App\Models\User\Student;
use App\Services\FeeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScholarshipController extends Controller
{
    public function assignScholarship(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'scholarship_id' => 'required|exists:scholarships,id',
            'academic_year' => 'required|string'
        ]);

        $student = Student::find($request->student_id);
        $scholarship = Scholarship::find($request->scholarship_id);

        // Calculate discount amount
        $discountAmount = $scholarship->type === 'percentage' 
            ? ($student->fees()->sum('total_amount') * $scholarship->value) / 100
            : $scholarship->value;

        if ($scholarship->max_amount) {
            $discountAmount = min($discountAmount, $scholarship->max_amount);
        }

        $studentScholarship = $student->scholarships()->updateOrCreate(
            [
                'scholarship_id' => $scholarship->id,
                'academic_year' => $request->academic_year
            ],
            ['discount_amount' => $discountAmount]
        );

        return response()->json([
            'success' => true,
            'message' => 'Scholarship assigned successfully',
            'data' => $studentScholarship
        ]);
    }

    public function calculateFee(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'fee_structure_id' => 'required|exists:fee_structures,id'
        ]);

        $feeStructure = \App\Models\Fee\FeeStructure::find($request->fee_structure_id);
        $calculation = FeeCalculationService::calculateFeeWithScholarship($student, $feeStructure);

        return response()->json([
            'success' => true,
            'data' => $calculation
        ]);
    }
}