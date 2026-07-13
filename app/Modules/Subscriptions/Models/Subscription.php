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
        'trial_ends_at', 'grace_period_ends_at', 'cancelled_at', 'suspended_at',
        'expired_at', 'cancellation_reason', 'metadata',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'plan_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'renewal_date' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'suspended_at' => 'datetime',
        'expired_at' => 'datetime',
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

    public function scopeTrialExpiring($query)
    {
        return $query->where('status', 'trial')
            ->whereDate('trial_ends_at', '<=', now());
    }

    public function scopeTrialEndingSoon($query, int $days = 7)
    {
        return $query->where('status', 'trial')
            ->whereDate('trial_ends_at', '<=', now()->addDays($days))
            ->whereDate('trial_ends_at', '>', now());
    }

    public function scopeInGracePeriod($query)
    {
        return $query->where('status', 'expired')
            ->whereDate('grace_period_ends_at', '>', now());
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

    public function isInGracePeriod(): bool
    {
        return $this->status === 'expired'
            && $this->grace_period_ends_at
            && $this->grace_period_ends_at->isFuture();
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'expired_at' => now(),
        ]);
    }

    public function applyGracePeriod(int $days = 3): void
    {
        $this->update([
            'grace_period_ends_at' => now()->addDays($days),
        ]);
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
