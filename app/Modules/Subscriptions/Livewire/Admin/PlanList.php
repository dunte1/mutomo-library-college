<?php

namespace App\Modules\Subscriptions\Livewire\Admin;

use App\Modules\Subscriptions\Models\Plan;
use Livewire\Component;
use Livewire\WithPagination;

class PlanList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = '';

    public string $billingCycle = '';

    protected $queryString = ['search', 'type', 'billingCycle'];

    public function toggleActive(Plan $plan): void
    {
        abort_unless(auth()->user()->can('manage-pricing'), 403);
        $plan->update(['is_active' => ! $plan->is_active]);

        activity()
            ->performedOn($plan)
            ->causedBy(auth()->user())
            ->log(($plan->is_active ? 'Activated' : 'Deactivated')." plan: {$plan->name}");

        $this->dispatch('notify', message: 'Plan status updated.', type: 'success');
    }

    public function deletePlan(Plan $plan): void
    {
        abort_unless(auth()->user()->can('manage-pricing'), 403);
        if ($plan->subscriptions()->exists()) {
            $this->dispatch('notify', message: 'Cannot delete plan with active subscriptions.', type: 'error');

            return;
        }

        $plan->delete();
        $this->dispatch('notify', message: 'Plan deleted.', type: 'success');
    }

    public function render()
    {
        $plans = Plan::withCount('subscriptions')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->type, fn ($q) => $q->ofType($this->type))
            ->when($this->billingCycle, fn ($q) => $q->where('billing_cycle', $this->billingCycle))
            ->orderBy('sort_order')
            ->paginate(15);

        return view('subscriptions::livewire.admin.plan-list', [
            'plans' => $plans,
        ])->layout('layouts.app');
    }
}
