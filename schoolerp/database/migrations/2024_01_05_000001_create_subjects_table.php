<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->string('name', 100);
            $table->string('code', 20);
            $table->integer('credits')->default(1);
            $table->enum('type', ['theory', 'practical', 'both'])->default('theory');
            $table->integer('max_marks')->default(100);
            $table->integer('passing_marks')->default(40);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'academic_year_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};