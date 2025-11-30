<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 50)->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['division_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};