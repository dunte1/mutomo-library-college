<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_number', 30)->unique();
            $table->string('type', 30);
            $table->string('payment_method', 30)->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->string('reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('completed');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('status');
            $table->index('paid_at');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('status', 20)->default('pending');
            $table->text('description')->nullable();
            $table->string('type', 30)->default('fine');
            $table->timestamp('issued_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 30)->unique();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('payment_method', 30);
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('merchant_request_id', 100)->nullable();
            $table->string('checkout_request_id', 100)->nullable();
            $table->string('mpesa_receipt', 50)->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->text('result_desc')->nullable();
            $table->text('callback_data')->nullable();
            $table->timestamps();

            $table->index('checkout_request_id');
            $table->index('mpesa_receipt');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('type', 50);
            $table->json('parameters')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('mpesa_transactions');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('transactions');
    }
};
