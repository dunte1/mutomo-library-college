<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('digital_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->string('mime_type', 100);
            $table->bigInteger('file_size')->nullable();
            $table->string('file_extension', 20)->nullable();
            $table->string('cover_image')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('digital_asset_categories')->nullOnDelete();
            $table->string('author', 255)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->string('isbn', 20)->nullable();
            $table->year('publication_year')->nullable();
            $table->string('language', 10)->default('en');
            $table->json('keywords')->nullable();
            $table->string('access_level', 30)->default('restricted');
            $table->boolean('allow_download')->default(true);
            $table->boolean('allow_printing')->default(false);
            $table->integer('times_downloaded')->default(0);
            $table->integer('times_viewed')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('file_type');
            $table->index('access_level');
            $table->index('is_active');
            $table->index('category_id');
        });

        Schema::create('reading_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('digital_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trackable_type')->nullable();
            $table->unsignedBigInteger('trackable_id')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('progress')->default(0);
            $table->integer('last_page')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['trackable_type', 'trackable_id']);
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('digital_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30)->default('similar_book');
            $table->decimal('score', 5, 2)->default(0);
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['book_id', 'type']);
            $table->index('score');
        });

        Schema::create('citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->text('citation_text');
            $table->string('style', 50)->default('apa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citations');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('reading_histories');
        Schema::dropIfExists('digital_assets');
        Schema::dropIfExists('digital_asset_categories');
    }
};
