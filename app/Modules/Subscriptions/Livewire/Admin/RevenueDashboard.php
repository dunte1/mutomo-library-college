<?php

namespace App\Modules\Subscriptions\Livewire\Admin;

use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Livewire\Component;

class RevenueDashboard extends Component
{
    public array $revenueStats = [];

    public array $planStats = [];

    public array $subscriptionStats = [];

    public function mount(SubscriptionService $subscriptionService): void
    {
        $this->revenueStats = $subscriptionService->getRevenueStats();

        $this->planStats = Plan::withCount('subscriptions')
            ->get()
            ->map(fn ($plan) => [
                'name' => $plan->name,
                'price' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'subscribers_count' => $plan->subscriptions_count,
                'is_active' => $plan->is_active,
            ])
            ->toArray();

        $this->subscriptionStats = [
            'total' => Subscription::count(),
            'active' => Subscription::active()->count(),
            'expired' => Subscription::expired()->count(),
            'trial' => Subscription::trial()->count(),
            'cancelled' => Subscription::cancelled()->count(),
            'suspended' => Subscription::suspended()->count(),
            'pending' => Subscription::pending()->count(),
        ];
    }

    public function render()
    {
        return view('subscriptions::livewire.admin.revenue-dashboard')
            ->layout('layouts.app');
    }
}
