<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Academic\Program;
use App\Models\Academic\Division;
use App\Models\Academic\AcademicSession;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return \Database\Factories\StudentFactory::new();
    }

    protected $fillable = [
        'user_id',
        'admission_number',
        'roll_number',
        'prn',
        'university_seat_number',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'religion',
        'caste',
        'category',
        'aadhar_number',
        'mobile_number',
        'email',
        'current_address',
        'permanent_address',
        'program_id',
        'academic_year',
        'division_id',
        'academic_session_id',
        'student_status',
        'admission_date',
        'photo_path',
        'signature_path'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'annual_income' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(StudentGuardian::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(\App\Models\Fee\StudentFee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('student_status', 'active');
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    public function scopeByAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }
}