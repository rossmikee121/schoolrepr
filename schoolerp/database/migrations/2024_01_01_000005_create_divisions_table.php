<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->string('division_name', 10); // 'A', 'B', 'C'
            $table->integer('max_students')->default(60);
            $table->unsignedBigInteger('class_teacher_id')->nullable(); // Class Teacher
            $table->string('classroom', 50)->nullable(); // 'Room 101', 'Hall A'
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['academic_year_id', 'division_name']);
            $table->index(['academic_year_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};