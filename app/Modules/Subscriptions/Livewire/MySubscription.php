<?php

namespace App\Modules\Subscriptions\Livewire;

use App\Modules\Subscriptions\Models\Subscription;
use Livewire\Component;

class MySubscription extends Component
{
    public function cancelSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::where('user_id', auth()->id())
            ->findOrFail($subscriptionId);

        if (!$subscription->isActive()) {
            $this->dispatch('notify', message: 'Only active subscriptions can be cancelled.', type: 'error');
            return;
        }

        $subscription->cancel('Cancelled by user');
        $this->dispatch('notify', message: 'Subscription cancelled successfully.', type: 'success');
    }

    public function render()
    {
        $subscriptions = Subscription::with('plan')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('subscriptions::livewire.my-subscription', [
            'subscriptions' => $subscriptions,
        ])->layout('layouts.app');
    }
}
