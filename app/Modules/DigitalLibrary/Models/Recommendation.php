<?php

namespace App\Modules\DigitalLibrary\Models;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = [
        'user_id', 'book_id', 'digital_asset_id',
        'type', 'score', 'reason', 'is_active', 'expires_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function digitalAsset(): BelongsTo
    {
        return $this->belongsTo(DigitalAsset::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeTop($query, int $limit = 10)
    {
        return $query->orderByDesc('score')->limit($limit);
    }

    public static function typeOptions(): array
    {
        return [
            'similar_book' => 'Similar Book',
            'based_on_history' => 'Based on Your History',
            'popular' => 'Popular in Your Department',
            'new_arrival' => 'New Arrival',
            'personalized' => 'Personalized Pick',
        ];
    }
}
