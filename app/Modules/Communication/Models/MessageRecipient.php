<?php

namespace App\Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageRecipient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'message_id',
        'recipient_id',
        'recipient_type',
        'copy_type',
        'is_read',
        'read_at',
        'delivered_at',
        'delivery_status',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function markAsDelivered(): void
    {
        $this->update(['delivery_status' => 'delivered', 'delivered_at' => now()]);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now(), 'delivery_status' => 'delivered']);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByRecipient($query, int $userId)
    {
        return $query->where('recipient_id', $userId);
    }
}
