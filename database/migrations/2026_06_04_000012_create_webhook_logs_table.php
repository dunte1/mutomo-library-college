<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 30); // stripe, mpesa
            $table->string('event_type', 100)->nullable();
            $table->text('payload')->nullable();
            $table->string('status', 30)->default('pending'); // pending, processed, failed
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'status']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
