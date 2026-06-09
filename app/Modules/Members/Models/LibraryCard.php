<?php

namespace App\Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryCard extends Model
{
    protected $fillable = [
        'member_id',
        'card_number',
        'qr_code',
        'barcode',
        'passport_photo',
        'status',
        'issued_at',
        'expires_at',
        'issued_by',
        'replaced_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function replacer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeReplaced($query)
    {
        return $query->where('status', 'replaced');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function markAsLost(): void
    {
        $this->update(['status' => 'lost']);
    }

    public function markAsReplaced(): void
    {
        $this->update(['status' => 'replaced']);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
}
