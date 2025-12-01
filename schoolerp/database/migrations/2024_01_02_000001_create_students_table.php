<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('admission_number', 50)->unique();
            $table->string('roll_number', 50)->unique(); // 2025/FYBCOM/A/001
            $table->string('prn', 50)->unique()->nullable(); // University PRN
            $table->string('university_seat_number', 20)->nullable();
            
            // Personal Information
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('blood_group', 5)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('caste', 50)->nullable();
            $table->enum('category', ['general', 'obc', 'sc', 'st', 'vjnt', 'nt', 'sbc'])->default('general');
            $table->string('aadhar_number', 12)->unique()->nullable();
            
            // Contact Information
            $table->string('mobile_number', 15)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            
            // Academic Information
            $table->foreignId('program_id')->constrained('programs');
            $table->string('academic_year', 20); // '2025-26'
            $table->foreignId('division_id')->constrained('divisions');
            $table->foreignId('academic_session_id')->constrained('academic_sessions');
            
            // Status
            $table->enum('student_status', ['active', 'graduated', 'dropped', 'suspended', 'tc_issued'])->default('active');
            $table->date('admission_date');
            
            // Documents
            $table->string('photo_path', 500)->nullable();
            $table->string('signature_path', 500)->nullable();
            
            // Soft delete
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['program_id', 'academic_year']);
            $table->index(['division_id', 'student_status']);
            $table->index(['student_status', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};