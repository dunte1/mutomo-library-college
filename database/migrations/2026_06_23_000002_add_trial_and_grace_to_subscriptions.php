<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('grace_period_ends_at')->nullable()->after('trial_ends_at');
            $table->timestamp('expired_at')->nullable()->after('suspended_at');

            $table->index('grace_period_ends_at');
            $table->index(['status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['status', 'end_date']);
            $table->dropIndex(['grace_period_ends_at']);
            $table->dropColumn(['grace_period_ends_at', 'expired_at']);
        });
    }
};
