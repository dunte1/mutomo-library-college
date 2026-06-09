<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('downloadable');
            $table->string('type', 50)->default('document');
            $table->string('title', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('was_throttled')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'dl_user_created_idx');
            $table->index(['downloadable_type', 'downloadable_id'], 'dl_downloadable_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_logs');
    }
};
