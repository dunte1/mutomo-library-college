<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['members', 'books', 'book_copies', 'categories', 'authors', 'publishers', 'subjects',
            'digital_assets', 'digital_asset_categories', 'borrow_records', 'reservations', 'fines',
            'transactions', 'invoices', 'receipts'];

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

        $softDeleteTables = ['members', 'books', 'book_copies', 'categories', 'authors', 'publishers',
            'digital_assets', 'departments', 'programs'];
        foreach ($softDeleteTables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_by')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['members', 'books', 'book_copies', 'categories', 'authors', 'publishers', 'subjects',
            'digital_assets', 'digital_asset_categories', 'borrow_records', 'reservations', 'fines',
            'transactions', 'invoices', 'receipts'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('created_by');
                    $t->dropConstrainedForeignId('updated_by');
                });
            }
        }

        $softDeleteTables = ['members', 'books', 'book_copies', 'categories', 'authors', 'publishers',
            'digital_assets', 'departments', 'programs'];
        foreach ($softDeleteTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('deleted_by');
                });
            }
        }
    }
};
