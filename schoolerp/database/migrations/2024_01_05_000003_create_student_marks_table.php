<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('examination_id')->constrained('examinations');
            $table->decimal('marks_obtained', 5, 2);
            $table->decimal('max_marks', 5, 2);
            $table->string('grade', 5)->nullable();
            $table->enum('result', ['pass', 'fail', 'absent'])->default('pass');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'examination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};