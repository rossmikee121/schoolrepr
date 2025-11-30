<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\DivisionFactory::new();
    }

    protected $fillable = [
        'academic_year_id',
        'division_name',
        'max_students',
        'class_teacher_id',
        'classroom',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_students' => 'integer',
    ];

    // Relationships
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(\App\Models\User\Student::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function getAvailableCapacity(): int
    {
        return $this->max_students - $this->students()->where('student_status', 'active')->count();
    }

    public function hasCapacity(): bool
    {
        return $this->getAvailableCapacity() > 0;
    }
}