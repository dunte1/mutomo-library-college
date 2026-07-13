<?php

namespace App\Modules\Subscriptions\Services;

use App\Mail\ExpirationNotice;
use App\Mail\PaymentConfirmation;
use App\Mail\RenewalReminder;
use App\Mail\SubscriptionActivation;
use App\Models\User;
use App\Modules\Communication\Services\SmsService;
use App\Modules\Communication\Services\WhatsAppService;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Services\BillingService;
use App\Modules\Finance\Services\FinanceService;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Settings\Models\Setting;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionService
{
    public function getTrialDays(): int
    {
        return (int) app(Setting::class)::value('trial_days', 7);
    }

    public function createTrialSubscription(User $user): ?Subscription
    {
        $trialDays = $this->getTrialDays();

        if ($trialDays <= 0) {
            return null;
        }

        $freePlan = Plan::active()->where('price', 0)->first();

        if (! $freePlan) {
            $freePlan = Plan::where('slug', 'free-trial')->orWhere('price', 0)->first();
        }

        if (! $freePlan) {
            return null;
        }

        $now = now();
        $trialEnd = $now->copy()->addDays($trialDays);

        return DB::transaction(function () use ($user, $freePlan, $now, $trialEnd, $trialDays) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $freePlan->id,
                'status' => 'trial',
                'start_date' => $now,
                'end_date' => $trialEnd,
                'renewal_date' => $trialEnd,
                'billing_cycle' => 'monthly',
                'auto_renew' => false,
                'trial_ends_at' => $trialEnd,
            ]);

            activity()
                ->performedOn($subscription)
                ->causedBy($user)
                ->log("Trial subscription created for {$trialDays} days");

            return $subscription;
        });
    }

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

            // Generate invoice
            try {
                $financeService = app(FinanceService::class);
                $invoice = $financeService->generateInvoice(
                    $subscription->user,
                    $amount,
                    'subscription',
                    "Subscription payment: {$subscription->plan->name}"
                );
                $invoice->update(['transaction_id' => $transaction->id]);
            } catch (\Throwable $e) {
                Log::warning("Failed to generate invoice for subscription payment: {$e->getMessage()}");
            }

            // Generate receipt
            try {
                $receipt = $financeService->generateReceipt($transaction);
            } catch (\Throwable $e) {
                Log::warning("Failed to generate receipt for subscription payment: {$e->getMessage()}");
            }

            // Auto-issue library card if member exists and doesn't have one
            try {
                $member = Member::where('user_id', $subscription->user_id)->first();
                if ($member && ! $member->libraryCard) {
                    $libraryCardService = app(LibraryCardService::class);
                    $libraryCardService->autoIssueCard($member);
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to auto-issue library card: {$e->getMessage()}");
            }

            // Send subscription activation email
            try {
                $user = $subscription->user;
                if ($user && $user->email) {
                    Mail::to($user->email)->queue(new SubscriptionActivation($subscription));
                }
                if ($user) {
                    $this->sendNotification(
                        $user,
                        'Subscription Activated',
                        "Your {$subscription->plan->name} subscription is now active. Welcome!"
                    );
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to send subscription activation email: {$e->getMessage()}");
            }

            // Send payment confirmation email
            try {
                $user = $subscription->user;
                if ($user && $user->email) {
                    Mail::to($user->email)->queue(new PaymentConfirmation($transaction, $paymentMethod));
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to send payment confirmation email: {$e->getMessage()}");
            }

            // Email receipt
            try {
                if ($transaction->receipt) {
                    $billingService = app(BillingService::class);
                    $billingService->emailReceipt($transaction->receipt);
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to email receipt: {$e->getMessage()}");
            }

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

                    // Send expiration notice
                    try {
                        $user = $subscription->user;
                        if ($user && $user->email) {
                            Mail::to($user->email)->queue(
                                new ExpirationNotice($subscription, 'expired')
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Failed to send expiration notice: {$e->getMessage()}");
                    }

                    // Log in-app notification
                    try {
                        app(NotificationService::class)->send(
                            $subscription->user,
                            'subscription',
                            'Membership Expired',
                            "Your {$subscription->plan->name} subscription has expired. Renew to regain access.",
                            'clock',
                            route('subscriptions.plans'),
                        );
                    } catch (\Throwable $e) {
                        Log::warning("Failed to send in-app expiration notice: {$e->getMessage()}");
                    }

                    $count++;
                }
            });

        return $count;
    }

    public function processTrialExpirations(): int
    {
        $count = 0;
        Subscription::trialExpiring()
            ->chunk(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    $trialDays = $this->getTrialDays();

                    if ($trialDays > 0) {
                        $subscription->applyGracePeriod(3);
                    }

                    $subscription->markAsExpired();

                    try {
                        $user = $subscription->user;
                        if ($user && $user->email) {
                            Mail::to($user->email)->queue(
                                new ExpirationNotice($subscription, 'expired')
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Failed to send trial expiration notice: {$e->getMessage()}");
                    }

                    try {
                        app(NotificationService::class)->send(
                            $subscription->user,
                            'subscription',
                            'Trial Expired',
                            "Your trial for {$subscription->plan->name} has ended. Subscribe to continue.",
                            'clock',
                            route('subscriptions.plans'),
                        );
                    } catch (\Throwable $e) {
                        Log::warning("Failed to send in-app trial expiration: {$e->getMessage()}");
                    }

                    $count++;
                }
            });

        return $count;
    }

    public function processGracePeriodExpirations(): int
    {
        $count = 0;
        Subscription::where('status', 'expired')
            ->whereDate('grace_period_ends_at', '<=', now())
            ->chunk(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    $subscription->suspend();

                    try {
                        $user = $subscription->user;
                        if ($user && $user->email) {
                            Mail::to($user->email)->queue(
                                new ExpirationNotice($subscription, 'expired')
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Failed to send grace period expiration notice: {$e->getMessage()}");
                    }

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

                    // Send renewal reminder
                    try {
                        $user = $subscription->user;
                        if ($user && $user->email) {
                            Mail::to($user->email)->queue(
                                new RenewalReminder($subscription, 0)
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Failed to send renewal reminder: {$e->getMessage()}");
                    }

                    $count++;
                }
            });

        return $count;
    }

    public function sendExpiringSoonNotifications(int $days = 7): int
    {
        $count = 0;

        // Active subscriptions expiring soon
        Subscription::expiringSoon($days)
            ->chunk(100, function ($subscriptions) use (&$count, $days) {
                foreach ($subscriptions as $subscription) {
                    $this->sendExpirationAlert($subscription, $days);
                    $count++;
                }
            });

        // Trial subscriptions ending soon
        Subscription::trialEndingSoon($days)
            ->chunk(100, function ($subscriptions) use (&$count, $days) {
                foreach ($subscriptions as $subscription) {
                    $this->sendExpirationAlert($subscription, $days);
                    $count++;
                }
            });

        return $count;
    }

    protected function sendExpirationAlert(Subscription $subscription, int $days): void
    {
        $remainingDays = (int) now()->diffInDays($subscription->end_date ?? $subscription->trial_ends_at, false);

        // Send expiring soon email
        try {
            $user = $subscription->user;
            if ($user && $user->email) {
                Mail::to($user->email)->queue(
                    new ExpirationNotice($subscription, 'expiring_soon', $remainingDays)
                );
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to send expiring soon notice: {$e->getMessage()}");
        }

        // In-app notification
        try {
            app(NotificationService::class)->send(
                $subscription->user,
                'subscription',
                'Trial Ending Soon',
                "Your {$subscription->plan->name} trial ends in {$remainingDays} day(s). Subscribe to keep your access.",
                'clock',
                route('subscriptions.my'),
            );
        } catch (\Throwable $e) {
            Log::warning("Failed to send in-app trial ending notice: {$e->getMessage()}");
        }

        // WhatsApp/SMS notification
        try {
            $user = $subscription->user;
            if ($user) {
                $this->sendNotification(
                    $user,
                    'Trial Ending Soon',
                    "Your {$subscription->plan->name} trial ends in {$remainingDays} day(s). Subscribe at " . route('subscriptions.plans') . " to keep your access."
                );
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to send trial ending WhatsApp/SMS: {$e->getMessage()}");
        }
    }

    public function getPlanCostSettings(): array
    {
        return [
            'individual_monthly' => (float) app(Setting::class)::value('individual_monthly_fee', 0),
            'individual_yearly' => (float) app(Setting::class)::value('individual_yearly_fee', 0),
            'school_monthly' => (float) app(Setting::class)::value('school_monthly_fee', 0),
            'school_yearly' => (float) app(Setting::class)::value('school_yearly_fee', 0),
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

    protected function sendNotification(User $user, string $subject, string $message): void
    {
        if ($user->phone) {
            try {
                app(WhatsAppService::class)->send($user->phone, $message);
            } catch (\Throwable $e) {
                Log::warning("WhatsApp notification failed: {$e->getMessage()}");
            }

            try {
                app(SmsService::class)->send($user->phone, $message);
            } catch (\Throwable $e) {
                Log::warning("SMS notification failed: {$e->getMessage()}");
            }
        }
    }

    protected function monthExpr(): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'mysql' => "DATE_FORMAT(paid_at, '%Y-%m')",
            'pgsql' => "TO_CHAR(paid_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', paid_at)",
            default => "DATE_FORMAT(paid_at, '%Y-%m')",
        };
    }

    /**
     * Get subscription revenue statistics.
     */
    public function getRevenueStats(): array
    {
        $subscriptionTransactions = Transaction::completed()
            ->whereIn('transactions.type', ['subscription_payment', 'subscription_renewal']);

        $currentMonth = $subscriptionTransactions->clone()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year);

        $previousMonth = $subscriptionTransactions->clone()
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year);

        $currentRevenue = (float) $currentMonth->sum('amount');
        $previousRevenue = (float) $previousMonth->sum('amount');

        $growthRate = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : 0;

        return [
            'total_revenue' => (float) $subscriptionTransactions->sum('amount'),
            'current_month_revenue' => $currentRevenue,
            'previous_month_revenue' => $previousRevenue,
            'monthly_growth_rate' => $growthRate,
            'current_month_transactions' => $currentMonth->count(),
            'total_transactions' => $subscriptionTransactions->count(),
            'revenue_by_plan' => $subscriptionTransactions->clone()
                ->join('subscriptions', 'transactions.subscription_id', '=', 'subscriptions.id')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->selectRaw('plans.name, SUM(transactions.amount) as total, COUNT(*) as count')
                ->groupBy('plans.name')
                ->get()
                ->toArray(),
            'monthly_breakdown' => $subscriptionTransactions->clone()
                ->selectRaw($this->monthExpr().' as month, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray(),
        ];
    }
}
