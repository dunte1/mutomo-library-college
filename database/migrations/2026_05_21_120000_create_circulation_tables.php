<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_copy_id')->constrained()->cascadeOnDelete();
            $table->timestamp('borrowed_at');
            $table->timestamp('due_at');
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->integer('renewal_count')->default(0);
            $table->integer('max_renewals')->default(2);
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('borrowed_at');
            $table->index('due_at');
            $table->index(['user_id', 'status']);
            $table->index(['book_copy_id', 'status']);
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('pending');
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['book_id', 'status']);
        });

        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_record_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('waived_amount', 10, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->string('reason', 100);
            $table->text('notes')->nullable();
            $table->timestamp('assessed_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('waived_at')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status']);
        });

        Schema::create('circulation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circulation_settings');
        Schema::dropIfExists('fines');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('borrow_records');
    }
};
