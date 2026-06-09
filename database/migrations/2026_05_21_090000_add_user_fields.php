<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->after('avatar');
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete()->after('department_id');
            $table->string('admission_number', 50)->nullable()->unique()->after('program_id');
            $table->string('employee_id', 50)->nullable()->unique()->after('admission_number');
            $table->string('academic_year', 20)->nullable()->after('employee_id');
            $table->integer('semester')->nullable()->after('academic_year');
            $table->boolean('is_active')->default(true)->after('semester');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('two_factor_enabled')->default(false)->after('last_login_ip');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->softDeletes();

            $table->index('admission_number');
            $table->index('employee_id');
            $table->index('is_active');
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'department_id', 'program_id',
                'admission_number', 'employee_id', 'academic_year', 'semester',
                'is_active', 'last_login_at', 'last_login_ip',
                'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes',
                'deleted_at',
            ]);
        });
    }
};
