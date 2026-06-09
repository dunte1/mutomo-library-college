<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('biography')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('photo')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
        });

        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('parent_id');
            $table->index('is_active');
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('isbn13', 20)->nullable()->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('language', 10)->default('en');
            $table->integer('pages')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('edition', 50)->nullable();
            $table->string('volume', 50)->nullable();
            $table->string('series')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('condition', 20)->default('good');
            $table->string('status', 20)->default('active');
            $table->foreignId('publisher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('dewey_decimal', 50)->nullable();
            $table->string('lc_classification', 50)->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('isbn');
            $table->index('isbn13');
            $table->index('title');
            $table->index('publication_year');
            $table->index('category_id');
            $table->index('publisher_id');
            $table->index('is_active');
        });

        Schema::create('book_author', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['book_id', 'author_id']);
        });

        Schema::create('book_subject', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['book_id', 'subject_id']);
        });

        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('barcode', 100)->unique();
            $table->string('rfid_tag', 100)->nullable()->unique();
            $table->string('shelf_location', 100)->nullable();
            $table->string('status', 20)->default('available');
            $table->string('condition', 20)->default('good');
            $table->date('acquired_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('barcode');
            $table->index('rfid_tag');
            $table->index('status');
            $table->index('shelf_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('book_subject');
        Schema::dropIfExists('book_author');
        Schema::dropIfExists('books');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
    }
};
