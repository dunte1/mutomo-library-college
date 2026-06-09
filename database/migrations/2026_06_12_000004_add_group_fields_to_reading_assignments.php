<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reading_assignments', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('viewed_at')->nullable()->after('completed_at');

            $table->index(['program_id']);
            $table->index(['department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reading_assignments', function (Blueprint $table) {
            $table->dropIndex(['program_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn(['program_id', 'department_id', 'viewed_at']);
        });
    }
};
