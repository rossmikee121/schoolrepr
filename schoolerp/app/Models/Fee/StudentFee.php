<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User\Student;

class StudentFee extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\StudentFeeFactory::new();
    }

    protected $fillable = [
        'student_id', 'fee_structure_id', 'total_amount', 'discount_amount', 
        'final_amount', 'paid_amount', 'outstanding_amount', 'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOutstanding($query)
    {
        return $query->where('outstanding_amount', '>', 0);
    }
}