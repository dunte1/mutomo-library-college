<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Shared\Traits\Auditable;

class Book extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'isbn',
        'title',
        'subtitle',
        'slug',
        'description',
        'language',
        'pages',
        'publication_year',
        'edition',
        'volume',
        'series',
        'cover_image',
        'condition',
        'status',
        'publisher_id',
        'category_id',
        'price',
        'dewey_decimal',
        'lc_classification',
        'tags',
        'is_active',
        'is_featured',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'pages' => 'integer',
            'publication_year' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_author')
            ->withTimestamps();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'book_subject')
            ->withTimestamps();
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    public function availableCopies(): HasMany
    {
        return $this->hasMany(BookCopy::class)->where('status', BookCopy::STATUS_AVAILABLE);
    }

    public function borrowedCopies(): HasMany
    {
        return $this->hasMany(BookCopy::class)->where('status', BookCopy::STATUS_BORROWED);
    }

    public function getAvailableCountAttribute(): int
    {
        return $this->availableCopies()->count();
    }

    public function getTotalCopiesAttribute(): int
    {
        return $this->copies()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('isbn', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhereHas('authors', function ($q) use ($term) {
                  $q->where('name', 'like', "%{$term}%");
              })
              ->orWhereHas('publisher', function ($q) use ($term) {
                  $q->where('name', 'like', "%{$term}%");
              })
              ->orWhereHas('subjects', function ($q) use ($term) {
                  $q->where('name', 'like', "%{$term}%");
              });
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->whereHas('authors', function ($q) use ($authorId) {
            $q->where('author_id', $authorId);
        });
    }

    public function scopeByPublisher($query, $publisherId)
    {
        return $query->where('publisher_id', $publisherId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->whereHas('subjects', function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        });
    }

    public function digitalAssets(): HasMany
    {
        return $this->hasMany(\App\Modules\DigitalLibrary\Models\DigitalAsset::class);
    }

    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(BookReview::class)->where('is_approved', true);
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('publication_year', $year);
    }
}
