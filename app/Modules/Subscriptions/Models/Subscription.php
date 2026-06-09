<?php

namespace App\Modules\Subscriptions\Models;

use App\Models\User;
use App\Modules\Finance\Models\Transaction;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }

    protected $fillable = [
        'user_id', 'plan_id', 'status', 'start_date', 'end_date',
        'renewal_date', 'billing_cycle', 'payment_method',
        'payment_gateway_subscription_id', 'auto_renew',
        'trial_ends_at', 'cancelled_at', 'suspended_at',
        'cancellation_reason', 'metadata',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'renewal_date' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'suspended_at' => 'datetime',
        'auto_renew' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeDueForRenewal($query)
    {
        return $query->where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('renewal_date', '<=', now());
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->whereDate('end_date', '<=', now()->addDays($days))
            ->whereDate('end_date', '>', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function renew(): void
    {
        $plan = $this->plan;
        $now = now();

        $startDate = $now;
        $endDate = $plan->isMonthly()
            ? $now->copy()->addMonth()
            : $now->copy()->addYear();

        $this->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'renewal_date' => $endDate,
            'status' => 'active',
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
            'cancellation_reason' => $reason,
        ]);
    }

    public function suspend(): void
    {
        $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);
    }

    public static function statusOptions(): array
    {
        return [
            'active' => 'Active',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'suspended' => 'Suspended',
            'pending' => 'Pending',
            'trial' => 'Trial',
        ];
    }
}
