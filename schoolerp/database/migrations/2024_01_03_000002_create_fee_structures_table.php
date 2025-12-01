<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs');
            $table->string('academic_year', 20);
            $table->foreignId('fee_head_id')->constrained('fee_heads');
            $table->decimal('amount', 10, 2);
            $table->integer('installments')->default(1); // 1, 2, 3, 4, 5
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'academic_year', 'fee_head_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};