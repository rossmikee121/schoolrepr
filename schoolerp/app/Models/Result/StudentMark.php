<?php

namespace App\Models\Result;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\Student;

class StudentMark extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\StudentMarkFactory::new();
    }

    protected $fillable = [
        'student_id', 'subject_id', 'examination_id', 'marks_obtained', 
        'max_marks', 'grade', 'result', 'is_approved'
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }

    public function getPercentageAttribute(): float
    {
        return ($this->marks_obtained / $this->max_marks) * 100;
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePassed($query)
    {
        return $query->where('result', 'pass');
    }
}