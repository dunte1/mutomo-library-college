<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['isbn13']);
            $table->dropIndex(['isbn13']);
            $table->dropColumn('isbn13');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn13', 20)->nullable()->unique()->after('isbn');
            $table->index('isbn13');
        });
    }
};
