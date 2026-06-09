<?php

namespace App\Modules\Subscriptions\Services;

use App\Models\User;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function createSubscription(User $user, Plan $plan, array $options = []): Subscription
    {
        $now = now();
        $endDate = $plan->isMonthly()
            ? $now->copy()->addMonth()
            : $now->copy()->addYear();

        return DB::transaction(function () use ($user, $plan, $now, $endDate, $options) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $options['status'] ?? 'pending',
                'start_date' => $now,
                'end_date' => $endDate,
                'renewal_date' => $endDate,
                'billing_cycle' => $plan->billing_cycle,
                'payment_method' => $options['payment_method'] ?? null,
                'auto_renew' => $options['auto_renew'] ?? true,
                'trial_ends_at' => $options['trial_ends_at'] ?? null,
                'metadata' => $options['metadata'] ?? null,
            ]);

            activity()
                ->performedOn($subscription)
                ->causedBy($user)
                ->withProperties(['plan' => $plan->name, 'amount' => $plan->price])
                ->log("Subscription created for plan: {$plan->name}");

            return $subscription;
        });
    }

    public function recordPayment(Subscription $subscription, string $paymentMethod, string $reference, float $amount): Transaction
    {
        return DB::transaction(function () use ($subscription, $paymentMethod, $reference, $amount) {
            $transaction = Transaction::create([
                'user_id' => $subscription->user_id,
                'transaction_number' => Transaction::generateNumber(),
                'type' => 'subscription_payment',
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'currency' => 'KES',
                'reference' => $reference,
                'description' => "Payment for {$subscription->plan->name} subscription",
                'status' => 'completed',
                'paid_at' => now(),
                'recorded_by' => null,
            ]);

            $subscription->update(['status' => 'active']);

            activity()
                ->performedOn($subscription)
                ->withProperties(['transaction_id' => $transaction->id, 'amount' => $amount, 'payment_method' => $paymentMethod])
                ->log("Subscription payment received: {$amount} via {$paymentMethod}");

            return $transaction;
        });
    }

    public function getUserActiveSubscription(User $user): ?Subscription
    {
        return Subscription::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    public function hasActiveSubscription(User $user): bool
    {
        return Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function cancelSubscription(Subscription $subscription, ?string $reason = null): void
    {
        $subscription->cancel($reason);

        activity()
            ->performedOn($subscription)
            ->causedBy(auth()->user())
            ->log("Subscription cancelled: {$reason}");
    }

    public function processExpiredSubscriptions(): int
    {
        $count = 0;
        Subscription::where('status', 'active')
            ->whereDate('end_date', '<', now())
            ->chunk(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    $subscription->markAsExpired();
                    $count++;
                }
            });

        return $count;
    }

    public function processDueRenewals(): int
    {
        $count = 0;
        Subscription::dueForRenewal()
            ->chunk(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    $subscription->renew();
                    $count++;
                }
            });

        return $count;
    }

    public function getPlanCostSettings(): array
    {
        return [
            'individual_monthly' => (float) app(\App\Modules\Settings\Models\Setting::class)::value('individual_monthly_fee', 0),
            'individual_yearly' => (float) app(\App\Modules\Settings\Models\Setting::class)::value('individual_yearly_fee', 0),
            'school_monthly' => (float) app(\App\Modules\Settings\Models\Setting::class)::value('school_monthly_fee', 0),
            'school_yearly' => (float) app(\App\Modules\Settings\Models\Setting::class)::value('school_yearly_fee', 0),
        ];
    }

    public function syncPlansFromSettings(): void
    {
        $pricing = $this->getPlanCostSettings();

        $planDefinitions = [
            ['name' => 'Individual Monthly', 'slug' => 'individual-monthly', 'type' => 'individual', 'billing_cycle' => 'monthly', 'price' => $pricing['individual_monthly']],
            ['name' => 'Individual Yearly', 'slug' => 'individual-yearly', 'type' => 'individual', 'billing_cycle' => 'yearly', 'price' => $pricing['individual_yearly']],
            ['name' => 'School Monthly', 'slug' => 'school-monthly', 'type' => 'school', 'billing_cycle' => 'monthly', 'price' => $pricing['school_monthly']],
            ['name' => 'School Yearly', 'slug' => 'school-yearly', 'type' => 'school', 'billing_cycle' => 'yearly', 'price' => $pricing['school_yearly']],
        ];

        foreach ($planDefinitions as $def) {
            Plan::updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'type' => $def['type'],
                    'billing_cycle' => $def['billing_cycle'],
                    'price' => $def['price'],
                    'currency' => 'KES',
                    'is_active' => $def['price'] > 0,
                ]
            );
        }
    }
}
