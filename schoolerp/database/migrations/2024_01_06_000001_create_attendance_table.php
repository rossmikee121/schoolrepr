<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->constrained('users');
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};