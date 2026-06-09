<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('type', 30); // individual, school
            $table->string('billing_cycle', 30); // monthly, yearly
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'billing_cycle']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
