<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\ProgramFactory::new();
    }
    protected $fillable = [
        'name',
        'short_name',
        'code',
        'university_affiliation',
        'university_program_code',
        'department_id',
        'duration_years',
        'total_semesters',
        'program_type',
        'default_grade_scale_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_years' => 'integer',
        'total_semesters' => 'integer',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
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

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}