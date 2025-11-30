<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roll_number_sequences', function (Blueprint $table) {
            $table->foreignId('program_id')->constrained('programs');
            $table->string('academic_year', 20); // '2025-26'
            $table->string('division_name', 10); // 'A', 'B', 'C'
            $table->integer('last_number')->default(0);
            $table->timestamps();

            $table->primary(['program_id', 'academic_year', 'division_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roll_number_sequences');
    }
};