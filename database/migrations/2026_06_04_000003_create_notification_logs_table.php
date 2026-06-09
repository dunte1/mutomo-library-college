<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('channel');
            $table->string('type');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['channel', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
