<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('id_number')->nullable()->unique();
            $table->enum('membership_type', ['student', 'teacher', 'staff', 'external'])->default('student');
            $table->enum('status', ['active', 'suspended', 'expired', 'inactive'])->default('active');
            $table->date('joined_at');
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
