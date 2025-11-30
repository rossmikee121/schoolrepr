<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // 'Bachelor of Commerce'
            $table->string('short_name', 100); // 'B.Com'
            $table->string('code', 20)->unique(); // 'BCOM'
            
            // University Information (Flexible)
            $table->string('university_affiliation', 100)->nullable(); // 'SPPU', 'Mumbai University'
            $table->string('university_program_code', 20)->nullable(); // For any university pattern
            
            $table->foreignId('department_id')->constrained('departments');
            $table->integer('duration_years'); // 3 for UG, 2 for PG
            $table->integer('total_semesters')->nullable(); // 6 for 3-year, 4 for 2-year
            $table->enum('program_type', ['undergraduate', 'postgraduate', 'diploma']);
            
            // Grading System
            $table->string('default_grade_scale_name', 100)->default('SPPU 10-Point');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['department_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};