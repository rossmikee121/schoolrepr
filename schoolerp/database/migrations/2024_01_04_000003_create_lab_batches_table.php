<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_session_id')->constrained('lab_sessions');
            $table->foreignId('student_id')->constrained('students');
            $table->boolean('is_present')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['lab_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_batches');
    }
};