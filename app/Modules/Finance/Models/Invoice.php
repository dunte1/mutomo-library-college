<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'transaction_id', 'amount', 'currency',
        'status', 'description', 'type', 'issued_at', 'due_at', 'paid_at', 'issued_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public static function generateNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid', 'paid_at' => now()]);
    }
}
