<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_copies', function (Blueprint $table) {
            $table->index('barcode', 'idx_book_copies_barcode');
            $table->index('status', 'idx_book_copies_status');
            $table->index(['book_id', 'status'], 'idx_book_copies_book_status');
        });
    }

    public function down(): void
    {
        Schema::table('book_copies', function (Blueprint $table) {
            $table->dropIndex('idx_book_copies_barcode');
            $table->dropIndex('idx_book_copies_status');
            $table->dropIndex('idx_book_copies_book_status');
        });
    }
};
