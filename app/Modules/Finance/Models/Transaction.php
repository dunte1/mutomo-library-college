<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'fine_id', 'subscription_id', 'transaction_number', 'type', 'payment_method',
        'amount', 'currency', 'reference', 'description', 'status', 'paid_at', 'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fine(): BelongsTo
    {
        return $this->belongsTo(Fine::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    public function mpesaTransaction(): HasOne
    {
        return $this->hasOne(MpesaTransaction::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public static function generateNumber(): string
    {
        return 'TXN-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));
    }

    public function scopeCompleted($query)
    {
        return $query->where('transactions.status', 'completed');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function typeOptions(): array
    {
        return ['fine_payment', 'lost_book_fine', 'damage_fine', 'donation', 'registration_fee', 'subscription_payment', 'subscription_renewal', 'other'];
    }

    public static function paymentMethodOptions(): array
    {
        return ['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'stripe' => 'Stripe', 'bank' => 'Bank Transfer', 'card' => 'Card', 'cheque' => 'Cheque'];
    }
}
