<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\Student;

class LabBatch extends Model
{
    use HasFactory;

    protected $fillable = ['lab_session_id', 'student_id', 'is_present', 'remarks'];

    protected $casts = [
        'is_present' => 'boolean',
    ];

    public function labSession(): BelongsTo
    {
        return $this->belongsTo(LabSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopePresent($query)
    {
        return $query->where('is_present', true);
    }
}