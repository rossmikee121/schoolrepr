<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_id')->constrained('student_fees');
            $table->string('receipt_number', 50)->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_mode', ['cash', 'online', 'cheque', 'dd']);
            $table->string('transaction_id', 100)->nullable();
            $table->date('payment_date');
            $table->enum('status', ['pending', 'success', 'failed'])->default('success');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['student_fee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};