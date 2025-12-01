<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_name', 20)->unique(); // '2025-26'
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_current', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_sessions');
    }
};