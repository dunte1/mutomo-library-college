<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('document_id', 50)->unique();
            $table->string('title', 255);
            $table->string('type', 50)->default('report');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('verification_count')->default(0);
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();

            $table->index('document_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
