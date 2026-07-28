<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['features', 'testimonials', 'why_choose_us', 'access_levels'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (! Schema::hasColumn($table, 'created_by')) {
                        $t->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
                    }
                    if (! Schema::hasColumn($table, 'updated_by')) {
                        $t->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                    }
                });
            }
        }

        // Add deleted_by for soft-deletable tables
        $softDeleteTables = ['features', 'testimonials', 'why_choose_us', 'access_levels'];
        foreach ($softDeleteTables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_by')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['features', 'testimonials', 'why_choose_us', 'access_levels'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('created_by');
                    $t->dropConstrainedForeignId('updated_by');
                    $t->dropConstrainedForeignId('deleted_by');
                });
            }
        }
    }
};
