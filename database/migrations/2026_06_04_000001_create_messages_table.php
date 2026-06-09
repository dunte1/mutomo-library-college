<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->enum('type', ['direct', 'group', 'broadcast', 'department', 'program'])->default('direct');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sent', 'failed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sender_id', 'status', 'created_at']);
            $table->index('scheduled_at');
        });

        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_type')->nullable();
            $table->enum('copy_type', ['to', 'cc', 'bcc'])->default('to');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->enum('delivery_status', ['pending', 'delivered', 'failed'])->default('pending');
            $table->timestamps();

            $table->index(['message_id', 'recipient_id']);
            $table->index(['recipient_id', 'is_read']);
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('messages');
    }
};
