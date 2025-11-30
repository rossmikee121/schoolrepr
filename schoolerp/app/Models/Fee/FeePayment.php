<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_fee_id', 'receipt_number', 'amount', 'payment_mode', 
        'transaction_id', 'payment_date', 'status', 'remarks'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }
}