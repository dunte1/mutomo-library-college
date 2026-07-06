<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('trial_days')->default(0)->after('sort_order');
            $table->integer('member_limit')->default(0)->after('trial_days');
            $table->integer('book_limit')->default(0)->after('member_limit');
            $table->integer('asset_limit')->default(0)->after('book_limit');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['trial_days', 'member_limit', 'book_limit', 'asset_limit']);
        });
    }
};
