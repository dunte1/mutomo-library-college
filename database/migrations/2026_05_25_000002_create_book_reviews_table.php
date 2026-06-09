<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating')->unsigned();
            $table->text('review')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            $table->unique(['book_id', 'user_id']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reviews');
    }
};
