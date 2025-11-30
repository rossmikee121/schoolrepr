<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGuardian extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\StudentGuardianFactory::new();
    }
    protected $fillable = [
        'student_id',
        'guardian_type',
        'full_name',
        'occupation',
        'annual_income',
        'mobile_number',
        'email',
        'photo_path',
        'relation',
        'address',
        'is_primary_contact'
    ];

    protected $casts = [
        'annual_income' => 'decimal:2',
        'is_primary_contact' => 'boolean',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Scopes
    public function scopePrimaryContact($query)
    {
        return $query->where('is_primary_contact', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('guardian_type', $type);
    }
}