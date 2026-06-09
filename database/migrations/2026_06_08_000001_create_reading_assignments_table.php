<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('digital_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('type', 20)->default('assignment');
            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_assignments');
    }
};
