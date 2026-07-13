<?php

namespace App\Modules\Catalog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BookReview extends Model
{
    protected $table = 'book_reviews';

    protected $fillable = [
        'book_id',
        'user_id',
        'rating',
        'review',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'book_id' => 'integer',
            'user_id' => 'integer',
            'rating' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }
}
