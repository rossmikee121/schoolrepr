<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('program_id')->constrained('programs');
            $table->json('header_config');
            $table->json('subject_config');
            $table->json('footer_config');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_templates');
    }
};