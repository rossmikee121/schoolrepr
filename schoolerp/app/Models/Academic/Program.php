<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'code',
        'department_id',
        'duration_years',
        'total_semesters',
        'program_type',
        'university_affiliation',
        'university_program_code',
        'default_grade_scale_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_years' => 'integer'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(\App\Models\User\Student::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }
}