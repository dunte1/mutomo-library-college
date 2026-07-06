<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 30); // stripe, mpesa, cash
            $table->string('gateway_reference')->nullable();
            $table->string('event_type', 100);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->string('status', 30)->default('pending');
            $table->text('request_payload')->nullable();
            $table->text('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'status']);
            $table->index('gateway_reference');
            $table->index('event_type');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
