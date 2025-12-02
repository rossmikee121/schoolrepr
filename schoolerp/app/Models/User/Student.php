<?php

/**
 * =============================================================================
 * STUDENT MODEL - Represents Student Data in Database
 * =============================================================================
 * 
 * This model represents the 'students' table in the database.
 * It handles all student-related data and relationships.
 * 
 * DATABASE TABLE: students
 * 
 * KEY RELATIONSHIPS:
 * - Belongs to User (for login credentials)
 * - Belongs to Program (B.Com, B.Sc, MBA, etc.)
 * - Belongs to Division (FY-A, SY-B, TY-C, etc.)
 * - Belongs to Academic Session (2024-25, etc.)
 * - Has Many Guardians (parents/guardians)
 * - Has Many Fees (fee records)
 * 
 * FOR INTERNS:
 * - Model = Represents a database table in code
 * - Relationships = How tables connect to each other
 * - Fillable = Which fields can be mass-assigned (security feature)
 * - Scopes = Reusable query filters
 * - Accessors = Computed properties (like full_name)
 * 
 * EXAMPLE USAGE:
 * $student = Student::find(1);  // Get student by ID
 * $student->program->name;      // Get student's program name
 * $student->full_name;          // Get computed full name
 * =============================================================================
 */

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
    // Traits (add extra functionality to the model)
    use HasFactory;    // Enables creating fake data for testing
    use SoftDeletes;   // Enables "soft delete" - marks as deleted without actually deleting

    /**
     * FACTORY METHOD - For creating test data
     * Used in testing and database seeding
     */
    protected static function newFactory()
    {
        return \Database\Factories\StudentFactory::new();
    }

    /**
     * FILLABLE FIELDS - Security Feature
     * 
     * These are the ONLY fields that can be mass-assigned using Student::create()
     * This prevents hackers from setting fields they shouldn't (like ID)
     * 
     * STUDENT IDENTIFICATION:
     * - admission_number: Unique college admission number
     * - roll_number: Class roll number (auto-generated)
     * - prn: Permanent Registration Number (university)
     * - university_seat_number: University exam seat number
     * 
     * PERSONAL INFO:
     * - first_name, middle_name, last_name: Student name
     * - date_of_birth, gender, blood_group: Basic info
     * - religion, caste, category: For scholarship eligibility
     * - aadhar_number: Government ID
     * 
     * CONTACT INFO:
     * - mobile_number, email: Communication
     * - current_address, permanent_address: Addresses
     * 
     * ACADEMIC INFO:
     * - program_id: Links to programs table (B.Com, B.Sc, etc.)
     * - academic_year: FY, SY, TY
     * - division_id: Links to divisions table (A, B, C)
     * - academic_session_id: Links to academic_sessions table (2024-25)
     * 
     * STATUS & DOCUMENTS:
     * - student_status: active, inactive, graduated, dropped
     * - admission_date: When student joined
     * - photo_path, signature_path: File paths for documents
     */
    protected $fillable = [
        'user_id',              // Links to users table for login
        'admission_number',     // College admission number
        'roll_number',          // Class roll number
        'prn',                  // University PRN
        'university_seat_number', // Exam seat number
        'first_name',           // Student first name
        'middle_name',          // Student middle name
        'last_name',            // Student last name
        'date_of_birth',        // Birth date
        'gender',               // Male/Female/Other
        'blood_group',          // A+, B+, O+, etc.
        'religion',             // Hindu, Muslim, Christian, etc.
        'caste',                // For government records
        'category',             // General, SC, ST, OBC (for scholarships)
        'aadhar_number',        // Government ID number
        'mobile_number',        // Contact number
        'email',                // Email address
        'current_address',      // Current living address
        'permanent_address',    // Permanent home address
        'program_id',           // Foreign key to programs table
        'academic_year',        // FY, SY, TY
        'division_id',          // Foreign key to divisions table
        'academic_session_id',  // Foreign key to academic_sessions table
        'student_status',       // active, inactive, graduated, dropped
        'admission_date',       // Date of admission
        'photo_path',           // Path to student photo file
        'signature_path'        // Path to student signature file
    ];

    /**
     * ATTRIBUTE CASTING - Data Type Conversion
     * 
     * Laravel automatically converts these database values to proper PHP types
     * 
     * EXAMPLES:
     * - Database stores '1995-05-15' as string
     * - Laravel converts to Carbon date object for easy manipulation
     * - $student->date_of_birth->format('d/m/Y') works automatically
     */
    protected $casts = [
        'date_of_birth' => 'date',      // String to Carbon date object
        'admission_date' => 'date',     // String to Carbon date object
        'annual_income' => 'decimal:2', // String to decimal with 2 places
    ];

    // ========================================
    // DATABASE RELATIONSHIPS
    // ========================================
    // These define how the students table connects to other tables
    
    /**
     * USER RELATIONSHIP - BelongsTo
     * 
     * Each student belongs to one user account (for login)
     * 
     * DATABASE: students.user_id -> users.id
     * USAGE: $student->user->email (get student's login email)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * PROGRAM RELATIONSHIP - BelongsTo
     * 
     * Each student belongs to one program (B.Com, B.Sc, MBA, etc.)
     * 
     * DATABASE: students.program_id -> programs.id
     * USAGE: $student->program->name (get "B.Com" or "B.Sc")
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * DIVISION RELATIONSHIP - BelongsTo
     * 
     * Each student belongs to one division (FY-A, SY-B, TY-C, etc.)
     * 
     * DATABASE: students.division_id -> divisions.id
     * USAGE: $student->division->name (get "FY-A" or "SY-B")
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * ACADEMIC SESSION RELATIONSHIP - BelongsTo
     * 
     * Each student belongs to one academic session (2024-25, 2025-26, etc.)
     * 
     * DATABASE: students.academic_session_id -> academic_sessions.id
     * USAGE: $student->academicSession->name (get "2024-25")
     */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /**
     * GUARDIANS RELATIONSHIP - HasMany
     * 
     * Each student can have multiple guardians (father, mother, etc.)
     * 
     * DATABASE: student_guardians.student_id -> students.id
     * USAGE: $student->guardians (get all parents/guardians)
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(StudentGuardian::class);
    }

    /**
     * FEES RELATIONSHIP - HasMany
     * 
     * Each student can have multiple fee records (different semesters, installments)
     * 
     * DATABASE: student_fees.student_id -> students.id
     * USAGE: $student->fees (get all fee records for this student)
     */
    public function fees(): HasMany
    {
        return $this->hasMany(\App\Models\Fee\StudentFee::class);
    }

    // ========================================
    // QUERY SCOPES - Reusable Filters
    // ========================================
    // These are pre-built filters that can be chained with queries
    
    /**
     * ACTIVE STUDENTS SCOPE
     * 
     * Filters to only show active students (not graduated/dropped)
     * 
     * USAGE: Student::active()->get() 
     * SQL: SELECT * FROM students WHERE student_status = 'active'
     */
    public function scopeActive($query)
    {
        return $query->where('student_status', 'active');
    }

    /**
     * BY PROGRAM SCOPE
     * 
     * Filters students by program (B.Com, B.Sc, etc.)
     * 
     * USAGE: Student::byProgram(1)->get()
     * SQL: SELECT * FROM students WHERE program_id = 1
     */
    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * BY DIVISION SCOPE
     * 
     * Filters students by division (FY-A, SY-B, etc.)
     * 
     * USAGE: Student::byDivision(1)->get()
     * SQL: SELECT * FROM students WHERE division_id = 1
     */
    public function scopeByDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    /**
     * BY ACADEMIC YEAR SCOPE
     * 
     * Filters students by academic year (FY, SY, TY)
     * 
     * USAGE: Student::byAcademicYear('FY')->get()
     * SQL: SELECT * FROM students WHERE academic_year = 'FY'
     */
    public function scopeByAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    // ========================================
    // ACCESSORS - Computed Properties
    // ========================================
    // These create virtual properties that don't exist in database
    
    /**
     * FULL NAME ACCESSOR
     * 
     * Combines first, middle, and last name into one property
     * 
     * USAGE: $student->full_name (automatically calls this method)
     * RESULT: "John Michael Smith" (combines all three names)
     * 
     * NOTE: Laravel automatically converts getFullNameAttribute() to full_name property
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }
}

/**
 * =============================================================================
 * STUDENT MODEL SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * WHAT THIS MODEL DOES:
 * 1. Represents the 'students' table in database
 * 2. Defines relationships to other tables (program, division, etc.)
 * 3. Provides reusable query filters (scopes)
 * 4. Creates computed properties (accessors)
 * 5. Handles data type conversion (casts)
 * 
 * COMMON USAGE EXAMPLES:
 * 
 * // Get all active students
 * $students = Student::active()->get();
 * 
 * // Get students in specific program and division
 * $students = Student::byProgram(1)->byDivision(2)->get();
 * 
 * // Get student with related data
 * $student = Student::with('program', 'division', 'guardians')->find(1);
 * 
 * // Access relationships
 * echo $student->program->name;     // "B.Com"
 * echo $student->division->name;    // "FY-A"
 * echo $student->full_name;         // "John Michael Smith"
 * 
 * // Get student's guardians
 * foreach($student->guardians as $guardian) {
 *     echo $guardian->name;
 * }
 * 
 * DATABASE TABLES CONNECTED:
 * - students (main table)
 * - users (for login credentials)
 * - programs (B.Com, B.Sc, etc.)
 * - divisions (FY-A, SY-B, etc.)
 * - academic_sessions (2024-25, etc.)
 * - student_guardians (parents info)
 * - student_fees (fee records)
 * =============================================================================
 */