<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->string('event_type');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['message_id', 'event_type']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_analytics');
    }
};
