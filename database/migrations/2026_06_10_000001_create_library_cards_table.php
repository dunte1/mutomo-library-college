<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('card_number', 50)->unique();
            $table->text('qr_code')->nullable();
            $table->text('barcode')->nullable();
            $table->string('passport_photo')->nullable();
            $table->enum('status', ['active', 'lost', 'replaced', 'expired'])->default('active');
            $table->date('issued_at');
            $table->date('expires_at')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->foreignId('replaced_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('card_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_cards');
    }
};
