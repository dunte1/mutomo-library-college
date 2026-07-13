<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reading_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('reading_histories', 'last_read_at')) {
                $table->timestamp('last_read_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reading_histories', function (Blueprint $table) {
            if (Schema::hasColumn('reading_histories', 'last_read_at')) {
                $table->dropColumn('last_read_at');
            }
        });
    }
};
