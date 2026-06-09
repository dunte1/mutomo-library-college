<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_successful')->default(true);
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('ip_address');
            $table->index('login_at');
            $table->index('is_successful');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
