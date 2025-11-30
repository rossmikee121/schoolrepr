<?php

namespace App\Http\Controllers\Api\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\StudentFee;
use App\Models\Fee\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function outstandingReport(Request $request): JsonResponse
    {
        $query = StudentFee::with(['student', 'feeStructure.feeHead'])
            ->where('outstanding_amount', '>', 0);

        if ($request->program_id) {
            $query->whereHas('student', fn($q) => $q->where('program_id', $request->program_id));
        }

        if ($request->academic_year) {
            $query->whereHas('student', fn($q) => $q->where('academic_year', $request->academic_year));
        }

        $outstandingFees = $query->get();
        $totalOutstanding = $outstandingFees->sum('outstanding_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'fees' => $outstandingFees,
                'total_outstanding' => $totalOutstanding,
                'count' => $outstandingFees->count()
            ]
        ]);
    }

    public function collectionReport(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $payments = FeePayment::with(['studentFee.student', 'studentFee.feeStructure.feeHead'])
            ->whereBetween('payment_date', [$request->from_date, $request->to_date])
            ->where('status', 'success')
            ->get();

        $summary = [
            'total_collection' => $payments->sum('amount'),
            'cash_collection' => $payments->where('payment_mode', 'cash')->sum('amount'),
            'online_collection' => $payments->where('payment_mode', 'online')->sum('amount'),
            'cheque_collection' => $payments->where('payment_mode', 'cheque')->sum('amount'),
            'total_receipts' => $payments->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments,
                'summary' => $summary
            ]
        ]);
    }

    public function defaulterReport(Request $request): JsonResponse
    {
        $daysOverdue = $request->days_overdue ?? 30;
        $overdueDate = Carbon::now()->subDays($daysOverdue);

        $defaulters = StudentFee::with(['student', 'feeStructure.feeHead'])
            ->where('outstanding_amount', '>', 0)
            ->where('created_at', '<', $overdueDate)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'defaulters' => $defaulters,
                'total_overdue' => $defaulters->sum('outstanding_amount'),
                'count' => $defaulters->count(),
                'days_overdue' => $daysOverdue
            ]
        ]);
    }
}