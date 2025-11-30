<?php

namespace App\Http\Controllers\Api\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\FeeHead;
use App\Models\Fee\FeeStructure;
use App\Models\Fee\StudentFee;
use App\Models\Fee\FeePayment;
use App\Models\User\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    public function assignFees(Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'fee_structure_ids' => 'required|array',
            'fee_structure_ids.*' => 'exists:fee_structures,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->student_ids as $studentId) {
                foreach ($request->fee_structure_ids as $feeStructureId) {
                    $feeStructure = FeeStructure::find($feeStructureId);
                    
                    StudentFee::updateOrCreate(
                        ['student_id' => $studentId, 'fee_structure_id' => $feeStructureId],
                        [
                            'total_amount' => $feeStructure->amount,
                            'final_amount' => $feeStructure->amount,
                            'outstanding_amount' => $feeStructure->amount,
                        ]
                    );
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Fees assigned successfully'
        ]);
    }

    public function recordPayment(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,online,cheque,dd',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $student) {
            $studentFee = StudentFee::where('student_id', $student->id)
                ->where('id', $request->student_fee_id)
                ->firstOrFail();

            // Generate receipt number
            $receiptNumber = 'RCP' . date('Y') . str_pad(FeePayment::count() + 1, 6, '0', STR_PAD_LEFT);

            // Create payment record
            $payment = FeePayment::create([
                'student_fee_id' => $studentFee->id,
                'receipt_number' => $receiptNumber,
                'amount' => $request->amount,
                'payment_mode' => $request->payment_mode,
                'transaction_id' => $request->transaction_id,
                'payment_date' => $request->payment_date,
                'remarks' => $request->remarks,
            ]);

            // Update student fee
            $studentFee->paid_amount += $request->amount;
            $studentFee->outstanding_amount = $studentFee->final_amount - $studentFee->paid_amount;
            $studentFee->status = $studentFee->outstanding_amount <= 0 ? 'paid' : 'partial';
            $studentFee->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment' => $payment,
                    'receipt_number' => $receiptNumber,
                    'outstanding_amount' => $studentFee->outstanding_amount
                ]
            ], 201);
        });
    }

    public function outstanding(Student $student): JsonResponse
    {
        $outstandingFees = $student->fees()
            ->with(['feeStructure.feeHead'])
            ->outstanding()
            ->get();

        $totalOutstanding = $outstandingFees->sum('outstanding_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'fees' => $outstandingFees,
                'total_outstanding' => $totalOutstanding
            ]
        ]);
    }
}