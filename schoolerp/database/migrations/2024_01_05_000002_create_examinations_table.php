<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->enum('type', ['internal', 'external', 'practical']);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('academic_year', 20);
            $table->enum('status', ['scheduled', 'ongoing', 'completed'])->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};