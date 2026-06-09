<?php

namespace App\Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationAnalytic extends Model
{
    protected $fillable = [
        'message_id',
        'event_type',
        'user_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
