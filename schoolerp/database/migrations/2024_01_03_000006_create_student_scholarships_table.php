<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('scholarship_id')->constrained('scholarships');
            $table->string('academic_year', 20);
            $table->decimal('discount_amount', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'scholarship_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scholarships');
    }
};