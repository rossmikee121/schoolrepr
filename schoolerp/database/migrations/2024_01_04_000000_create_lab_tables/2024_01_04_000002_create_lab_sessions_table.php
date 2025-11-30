<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('labs');
            $table->foreignId('division_id')->constrained('divisions');
            $table->string('subject_name', 100);
            $table->integer('batch_number');
            $table->integer('max_students');
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('instructor_id')->nullable()->constrained('users');
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();

            $table->index(['lab_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_sessions');
    }
};