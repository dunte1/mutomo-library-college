<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('admission_number')->nullable()->unique()->after('id_number');
            $table->foreignId('department_id')->nullable()->after('admission_number')->constrained()->nullOnDelete();
            $table->foreignId('program_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('admission_number');
        });
    }
};
