<?php

namespace App\Http\Controllers\Api\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\StudentFee;
use App\Models\Fee\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    private $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $studentFee = StudentFee::with('student')->find($request->student_fee_id);
        
        $orderData = [
            'receipt' => 'fee_' . $studentFee->id . '_' . time(),
            'amount' => $request->amount * 100, // Convert to paise
            'currency' => 'INR',
            'notes' => [
                'student_id' => $studentFee->student_id,
                'student_name' => $studentFee->student->full_name,
                'fee_type' => 'student_fee'
            ]
        ];

        $order = $this->razorpay->order->create($orderData);

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order['id'],
                'amount' => $request->amount,
                'currency' => 'INR',
                'key' => config('services.razorpay.key')
            ]
        ]);
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
            'student_fee_id' => 'required|exists:student_fees,id'
        ]);

        try {
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            $this->razorpay->utility->verifyPaymentSignature($attributes);

            // Get payment details
            $payment = $this->razorpay->payment->fetch($request->razorpay_payment_id);
            $studentFee = StudentFee::find($request->student_fee_id);

            // Record payment
            $receiptNumber = 'RCP' . date('Y') . str_pad(FeePayment::count() + 1, 6, '0', STR_PAD_LEFT);
            
            $feePayment = FeePayment::create([
                'student_fee_id' => $studentFee->id,
                'receipt_number' => $receiptNumber,
                'amount' => $payment['amount'] / 100, // Convert from paise
                'payment_mode' => 'online',
                'transaction_id' => $request->razorpay_payment_id,
                'payment_date' => now(),
                'status' => 'success'
            ]);

            // Update student fee
            $studentFee->paid_amount += $feePayment->amount;
            $studentFee->outstanding_amount = $studentFee->final_amount - $studentFee->paid_amount;
            $studentFee->status = $studentFee->outstanding_amount <= 0 ? 'paid' : 'partial';
            $studentFee->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => [
                    'receipt_number' => $receiptNumber,
                    'amount' => $feePayment->amount,
                    'outstanding' => $studentFee->outstanding_amount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
            ], 400);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        $webhookSignature = $request->header('X-Razorpay-Signature');
        
        $body = $request->getContent();
        
        try {
            $this->razorpay->utility->verifyWebhookSignature($body, $webhookSignature, $webhookSecret);
            
            $event = $request->all();
            
            if ($event['event'] === 'payment.captured') {
                // Handle successful payment
                $paymentId = $event['payload']['payment']['entity']['id'];
                
                FeePayment::where('transaction_id', $paymentId)
                    ->update(['status' => 'success']);
            }
            
            return response()->json(['status' => 'ok']);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }
}