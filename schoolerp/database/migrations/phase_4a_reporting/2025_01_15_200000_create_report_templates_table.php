<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('category'); // student, fee, academic, administrative
            $table->json('configuration'); // Complete report configuration
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};