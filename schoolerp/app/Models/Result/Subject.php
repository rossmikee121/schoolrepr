<?php

namespace App\Models\Result;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Academic\Program;
use App\Models\Academic\AcademicYear;

class Subject extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\SubjectFactory::new();
    }

    protected $fillable = [
        'program_id', 'academic_year_id', 'name', 'code', 'credits', 
        'type', 'max_marks', 'passing_marks', 'is_active'
    ];

    protected $casts = [
        'credits' => 'integer',
        'max_marks' => 'integer',
        'passing_marks' => 'integer',
        'is_active' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentMarks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}