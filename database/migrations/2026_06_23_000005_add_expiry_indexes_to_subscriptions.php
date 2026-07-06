<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['status', 'trial_ends_at'], 'idx_subscriptions_trial');
            $table->index(['status', 'grace_period_ends_at'], 'idx_subscriptions_grace');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_trial');
            $table->dropIndex('idx_subscriptions_grace');
        });
    }
};
