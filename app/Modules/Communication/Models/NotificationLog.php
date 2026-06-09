<?php

namespace App\Modules\Communication\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'channel',
        'type',
        'title',
        'body',
        'status',
        'sent_at',
        'read_at',
        'error',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
