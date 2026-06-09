<?php

namespace App\Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'gateway', 'event_type', 'payload', 'status', 'error', 'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
