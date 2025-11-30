<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('format'); // excel, pdf, csv
            $table->string('status'); // pending, processing, completed, failed
            $table->string('file_path')->nullable();
            $table->json('configuration'); // Report configuration used
            $table->foreignId('user_id')->constrained();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};