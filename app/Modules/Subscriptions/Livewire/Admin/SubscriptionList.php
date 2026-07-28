<?php

namespace App\Modules\Subscriptions\Livewire\Admin;

use App\Modules\Subscriptions\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $billingCycle = '';

    protected $queryString = ['search', 'status', 'billingCycle'];

    public function cancelSubscription(Subscription $subscription): void
    {
        abort_unless(auth()->user()->can('manage-subscriptions'), 403);
        $subscription->cancel('Cancelled by admin');
        $this->dispatch('notify', message: 'Subscription cancelled.', type: 'success');
    }

    public function suspendSubscription(Subscription $subscription): void
    {
        abort_unless(auth()->user()->can('manage-subscriptions'), 403);
        $subscription->suspend();
        $this->dispatch('notify', message: 'Subscription suspended.', type: 'success');
    }

    public function activateSubscription(Subscription $subscription): void
    {
        abort_unless(auth()->user()->can('manage-subscriptions'), 403);
        $subscription->activate();
        $this->dispatch('notify', message: 'Subscription activated.', type: 'success');
    }

    public function processPayment(Subscription $subscription): void
    {
        abort_unless(auth()->user()->can('process-subscription-payments'), 403);
        $subscription->activate();
        $this->dispatch('notify', message: 'Payment confirmed and subscription activated.', type: 'success');
    }

    public function render()
    {
        $subscriptions = Subscription::with('user', 'plan')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->billingCycle, fn ($q) => $q->where('billing_cycle', $this->billingCycle))
            ->latest()
            ->paginate(15);

        return view('subscriptions::livewire.admin.subscription-list', [
            'subscriptions' => $subscriptions,
        ])->layout('layouts.app');
    }
}
