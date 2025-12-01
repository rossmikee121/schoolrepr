<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // 'Commerce', 'Science', 'Management'
            $table->string('code', 20)->unique(); // 'COM', 'SCI', 'MGT'
            $table->unsignedBigInteger('hod_user_id')->nullable(); // Head of Department
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};