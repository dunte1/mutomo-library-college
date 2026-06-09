<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DownloadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'downloadable_type', 'downloadable_id', 'type', 'title',
        'ip_address', 'user_agent', 'was_throttled',
    ];

    protected $casts = [
        'was_throttled' => 'boolean',
    ];

    public function downloadable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeRecentForUser($query, int $userId, int $minutes = 60)
    {
        return $query->where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
