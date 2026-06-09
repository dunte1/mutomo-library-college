<?php

namespace App\Modules\DigitalLibrary\Models;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReadingHistory extends Model
{
    protected $fillable = [
        'user_id', 'digital_asset_id', 'book_id',
        'trackable_type', 'trackable_id',
        'started_at', 'completed_at', 'progress', 'last_page', 'duration_minutes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'integer',
        'last_page' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function digitalAsset(): BelongsTo
    {
        return $this->belongsTo(DigitalAsset::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(DigitalAsset::class, 'digital_asset_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at')->where('progress', '<', 100);
    }

    public function scopeCompleted($query)
    {
        return $query->where('progress', '>=', 100);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
