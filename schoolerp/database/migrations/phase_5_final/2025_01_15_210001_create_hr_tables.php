<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff profiles table
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('emergency_contact')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->text('address');
            $table->date('joining_date');
            $table->string('designation');
            $table->foreignId('department_id')->constrained();
            $table->enum('employment_type', ['permanent', 'contract', 'part_time']);
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->timestamps();
            
            $table->index(['department_id', 'status']);
            $table->index('employee_id');
        });

        // Salary structures table
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('hra', 10, 2)->default(0);
            $table->decimal('da', 10, 2)->default(0);
            $table->decimal('other_allowances', 10, 2)->default(0);
            $table->decimal('pf_deduction', 10, 2)->default(0);
            $table->decimal('tax_deduction', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('designation');
        });

        // Staff salaries table
        Schema::create('staff_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff_profiles');
            $table->foreignId('salary_structure_id')->constrained();
            $table->year('salary_year');
            $table->tinyInteger('salary_month');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('total_allowances', 10, 2);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->unique(['staff_id', 'salary_year', 'salary_month']);
            $table->index(['salary_year', 'salary_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_salaries');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('staff_profiles');
    }
};