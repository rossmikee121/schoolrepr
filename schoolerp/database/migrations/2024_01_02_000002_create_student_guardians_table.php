<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('guardian_type', ['father', 'mother', 'guardian']);
            
            $table->string('full_name');
            $table->string('occupation', 100)->nullable();
            $table->decimal('annual_income', 12, 2)->nullable();
            $table->string('mobile_number', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path', 500)->nullable();
            
            // Guardian-specific fields
            $table->string('relation', 50)->nullable(); // Only for guardian type
            $table->text('address')->nullable();
            
            $table->boolean('is_primary_contact')->default(false);
            $table->timestamps();

            // Ensure only one of each type per student
            $table->unique(['student_id', 'guardian_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};