<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs');
            $table->integer('year_number'); // 1, 2, 3
            $table->string('year_name', 50); // 'FY', 'SY', 'TY'
            
            $table->integer('semester_start')->nullable(); // 1 (for FY: Sem 1, 2)
            $table->integer('semester_end')->nullable(); // 2 (for FY: Sem 1, 2)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'year_number']);
            $table->index(['program_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};