<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->index('status');
            $table->index('membership_type');
            $table->index('joined_at');
            $table->index('expires_at');
            $table->index('registered_by');
            $table->index('first_name');
            $table->index('last_name');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index('reserved_at');
            $table->index('expires_at');
        });

        Schema::table('fines', function (Blueprint $table) {
            $table->index('assessed_at');
            $table->index('paid_at');
        });

        Schema::table('book_reviews', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['membership_type']);
            $table->dropIndex(['joined_at']);
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['registered_by']);
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['reserved_at']);
            $table->dropIndex(['expires_at']);
        });

        Schema::table('fines', function (Blueprint $table) {
            $table->dropIndex(['assessed_at']);
            $table->dropIndex(['paid_at']);
        });

        Schema::table('book_reviews', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
