<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\AcademicSessionFactory::new();
    }
    protected $fillable = [
        'session_name',
        'start_date',
        'end_date',
        'is_current',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function students(): HasMany
    {
        return $this->hasMany(\App\Models\User\Student::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    // Methods
    public static function getCurrentSession()
    {
        return static::current()->first();
    }
}