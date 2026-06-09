<?php

namespace App\Modules\Subscriptions\Livewire;

use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Livewire\Component;

class SubscriptionPlans extends Component
{
    public function subscribe(Plan $plan): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->redirectRoute('subscriptions.checkout', ['plan' => $plan->id]);
    }

    public function render()
    {
        $individualMonthly = Plan::active()->ofType('individual')->monthly()->first();
        $individualYearly = Plan::active()->ofType('individual')->yearly()->first();
        $schoolMonthly = Plan::active()->ofType('school')->monthly()->first();
        $schoolYearly = Plan::active()->ofType('school')->yearly()->first();

        $activeSubscription = null;
        if (auth()->check()) {
            $activeSubscription = Subscription::with('plan')
                ->where('user_id', auth()->id())
                ->whereIn('status', ['active', 'trial'])
                ->latest('id')
                ->first();
        }

        return view('subscriptions::livewire.subscription-plans', [
            'individualMonthly' => $individualMonthly,
            'individualYearly' => $individualYearly,
            'schoolMonthly' => $schoolMonthly,
            'schoolYearly' => $schoolYearly,
            'activeSubscription' => $activeSubscription,
        ])->layout('layouts.app');
    }
}
