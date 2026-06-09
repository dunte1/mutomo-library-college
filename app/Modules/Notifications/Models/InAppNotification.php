<?php

namespace App\Modules\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InAppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'icon',
        'action_url',
        'data',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'json',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function getNotificationIconAttribute(): string
    {
        return match ($this->icon ?? $this->type) {
            'overdue' => 'exclamation-circle',
            'due_reminder' => 'clock',
            'hold_available' => 'bookmark',
            'fine' => 'credit-card',
            'reservation' => 'calendar',
            'return' => 'archive',
            'system' => 'information-circle',
            default => 'bell',
        };
    }
}
