<?php

/**
 * =============================================================================
 * STUDENTS TABLE MIGRATION - Database Structure for Student Records
 * =============================================================================
 * 
 * This migration creates the 'students' table which is the core table for
 * storing all student information in the School ERP system.
 * 
 * TABLE PURPOSE:
 * - Store complete student profiles and academic information
 * - Link students to programs, divisions, and academic sessions
 * - Track student status throughout their academic journey
 * - Store contact information and documents
 * 
 * RELATIONSHIPS:
 * - Belongs to User (for login credentials)
 * - Belongs to Program (B.Com, B.Sc, MBA, etc.)
 * - Belongs to Division (FY-A, SY-B, TY-C, etc.)
 * - Belongs to Academic Session (2024-25, 2025-26, etc.)
 * - Has Many Guardians (parents/guardians)
 * - Has Many Fees (fee records)
 * - Has Many Marks (exam results)
 * - Has Many Attendance (attendance records)
 * 
 * BUSINESS RULES:
 * - Each student must have unique admission number
 * - Each student must have unique roll number
 * - Students can have optional PRN (university registration)
 * - Students belong to one program and one division
 * - Student status tracks their academic lifecycle
 * 
 * FOR INTERNS:
 * - Migration = Instructions for creating database table
 * - Blueprint = Laravel's way to define table structure
 * - Foreign Key = Links to other tables
 * - Index = Makes queries faster on specific columns
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CREATE STUDENTS TABLE
     * 
     * This method defines the structure of the students table.
     * It will be executed when running 'php artisan migrate'.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            // ========================================
            // PRIMARY KEY AND IDENTIFICATION
            // ========================================
            
            $table->id();  // Auto-incrementing primary key (1, 2, 3, ...)
            
            // Link to users table for login credentials
            // CASCADE DELETE: If user is deleted, student record is also deleted
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // UNIQUE IDENTIFIERS
            $table->string('admission_number', 50)->unique();     // College admission number (ADM2024001)
            $table->string('roll_number', 50)->unique();          // Class roll number (2025/FYBCOM/A/001)
            $table->string('prn', 50)->unique()->nullable();      // University PRN (Permanent Registration Number)
            $table->string('university_seat_number', 20)->nullable(); // Exam seat number
            
            // ========================================
            // PERSONAL INFORMATION
            // ========================================
            
            // NAME FIELDS
            $table->string('first_name', 100);          // Required: Student's first name
            $table->string('middle_name', 100)->nullable(); // Optional: Middle name (common in India)
            $table->string('last_name', 100);           // Required: Student's last name
            
            // BASIC PERSONAL DATA
            $table->date('date_of_birth');              // Birth date for age calculation
            $table->enum('gender', ['male', 'female', 'other']); // Gender options
            $table->string('blood_group', 5)->nullable(); // A+, B+, O+, AB+, etc.
            
            // DEMOGRAPHIC INFORMATION (Important for Indian institutions)
            $table->string('religion', 50)->nullable();  // Hindu, Muslim, Christian, etc.
            $table->string('caste', 50)->nullable();     // For government records
            
            // CATEGORY (Used for scholarships and reservations)
            // general = General category
            // obc = Other Backward Classes
            // sc = Scheduled Caste
            // st = Scheduled Tribe
            // vjnt = Vimukta Jati Nomadic Tribes
            // nt = Nomadic Tribes
            // sbc = Special Backward Classes
            $table->enum('category', ['general', 'obc', 'sc', 'st', 'vjnt', 'nt', 'sbc'])->default('general');
            
            // GOVERNMENT IDENTIFICATION
            $table->string('aadhar_number', 12)->unique()->nullable(); // Unique Aadhar number
            
            // ========================================
            // CONTACT INFORMATION
            // ========================================
            
            $table->string('mobile_number', 15)->nullable(); // Phone number for communication
            $table->string('email', 255)->nullable();        // Email address (optional)
            $table->text('current_address')->nullable();     // Where student currently lives
            $table->text('permanent_address')->nullable();   // Permanent home address
            
            // ========================================
            // ACADEMIC INFORMATION
            // ========================================
            
            // PROGRAM RELATIONSHIP
            // Links to programs table (B.Com, B.Sc, MBA, etc.)
            $table->foreignId('program_id')->constrained('programs');
            
            // ACADEMIC YEAR
            // Stores which year student is in: FY, SY, TY
            $table->string('academic_year', 20); // '2025-26'
            
            // DIVISION RELATIONSHIP
            // Links to divisions table (FY-A, SY-B, TY-C, etc.)
            $table->foreignId('division_id')->constrained('divisions');
            
            // ACADEMIC SESSION RELATIONSHIP
            // Links to academic_sessions table (2024-25, 2025-26, etc.)
            $table->foreignId('academic_session_id')->constrained('academic_sessions');
            
            // ========================================
            // STATUS AND LIFECYCLE
            // ========================================
            
            // STUDENT STATUS (Tracks academic lifecycle)
            // active = Currently studying
            // graduated = Successfully completed course
            // dropped = Left before completion
            // suspended = Temporarily not allowed to attend
            // tc_issued = Transfer Certificate issued (leaving)
            $table->enum('student_status', ['active', 'graduated', 'dropped', 'suspended', 'tc_issued'])->default('active');
            
            $table->date('admission_date'); // When student joined the institution
            
            // ========================================
            // DOCUMENT STORAGE
            // ========================================
            
            // File paths for uploaded documents
            $table->string('photo_path', 500)->nullable();     // Student photograph
            $table->string('signature_path', 500)->nullable(); // Student signature
            
            // ========================================
            // SYSTEM FIELDS
            // ========================================
            
            // SOFT DELETE
            // Allows "deleting" records without actually removing them
            // Deleted records have deleted_at timestamp, others have null
            $table->timestamp('deleted_at')->nullable();
            
            // TIMESTAMPS
            // created_at = When record was created
            // updated_at = When record was last modified
            $table->timestamps();

            // ========================================
            // DATABASE INDEXES (Performance Optimization)
            // ========================================
            
            // COMPOSITE INDEXES for faster queries
            // These make common queries much faster by pre-sorting data
            
            // Index for finding students by program and year
            // Used in queries like: "Get all FY B.Com students"
            $table->index(['program_id', 'academic_year']);
            
            // Index for finding students by division and status
            // Used in queries like: "Get all active students in FY-A"
            $table->index(['division_id', 'student_status']);
            
            // Index for finding active/inactive students
            // Used in queries like: "Get all active students" (excluding soft-deleted)
            $table->index(['student_status', 'deleted_at']);
        });
    }

    /**
     * ROLLBACK MIGRATION
     * 
     * This method defines what happens when migration is rolled back.
     * It will be executed when running 'php artisan migrate:rollback'.
     */
    public function down(): void
    {
        // Drop the students table if it exists
        Schema::dropIfExists('students');
    }
};

/**
 * =============================================================================
 * STUDENTS TABLE MIGRATION SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * WHAT THIS MIGRATION CREATES:
 * 1. Students table with comprehensive student information
 * 2. Foreign key relationships to other tables
 * 3. Indexes for query performance optimization
 * 4. Proper data types and constraints
 * 
 * TABLE STRUCTURE:
 * - Primary Key: id (auto-increment)
 * - Foreign Keys: user_id, program_id, division_id, academic_session_id
 * - Unique Fields: admission_number, roll_number, prn, aadhar_number
 * - Required Fields: first_name, last_name, date_of_birth, gender
 * - Optional Fields: middle_name, email, addresses, documents
 * 
 * RELATIONSHIPS CREATED:
 * - students.user_id -> users.id (login credentials)
 * - students.program_id -> programs.id (B.Com, B.Sc, etc.)
 * - students.division_id -> divisions.id (FY-A, SY-B, etc.)
 * - students.academic_session_id -> academic_sessions.id (2024-25, etc.)
 * 
 * BUSINESS LOGIC ENFORCED:
 * - Unique admission numbers (no duplicates)
 * - Unique roll numbers (no duplicates)
 * - Cascade delete (if user deleted, student deleted)
 * - Default status is 'active'
 * - Soft delete capability (records marked as deleted, not removed)
 * 
 * PERFORMANCE OPTIMIZATIONS:
 * - Indexes on commonly queried columns
 * - Composite indexes for multi-column queries
 * - Proper data types to minimize storage
 * 
 * RUNNING THIS MIGRATION:
 * php artisan migrate
 * 
 * ROLLING BACK THIS MIGRATION:
 * php artisan migrate:rollback
 * 
 * CHECKING MIGRATION STATUS:
 * php artisan migrate:status
 * 
 * EXAMPLE QUERIES AFTER MIGRATION:
 * - SELECT * FROM students WHERE student_status = 'active'
 * - SELECT * FROM students WHERE program_id = 1 AND academic_year = 'FY'
 * - SELECT * FROM students WHERE division_id = 1 AND student_status = 'active'
 * =============================================================================
 */