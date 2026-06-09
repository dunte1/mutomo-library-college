<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('pending'); // active, expired, cancelled, suspended, pending, trial
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->timestamp('renewal_date')->nullable();
            $table->string('billing_cycle', 30); // monthly, yearly
            $table->string('payment_method', 30)->nullable(); // mpesa, stripe, cash, etc.
            $table->string('payment_gateway_subscription_id')->nullable(); // Stripe subscription ID
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['plan_id', 'status']);
            $table->index('status');
            $table->index('end_date');
            $table->index('renewal_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
