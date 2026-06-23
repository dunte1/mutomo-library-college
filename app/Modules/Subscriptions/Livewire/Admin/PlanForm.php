<?php

namespace App\Modules\Subscriptions\Livewire\Admin;

use App\Modules\Subscriptions\Models\Plan;
use Livewire\Component;

class PlanForm extends Component
{
    public ?Plan $plan = null;

    public string $name = '';

    public string $type = 'individual';

    public string $billingCycle = 'monthly';

    public float $price = 0;

    public string $currency = 'KES';

    public string $description = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    public string $features = '';

    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:individual,school'],
            'billingCycle' => ['required', 'in:monthly,yearly'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
            'features' => ['nullable', 'string'],
        ];
    }

    public function mount(?Plan $plan = null): void
    {
        if ($plan && $plan->exists) {
            $this->editing = true;
            $this->plan = $plan;
            $this->name = $plan->name;
            $this->type = $plan->type;
            $this->billingCycle = $plan->billing_cycle;
            $this->price = (float) $plan->price;
            $this->currency = $plan->currency;
            $this->description = $plan->description ?? '';
            $this->isActive = $plan->is_active;
            $this->sortOrder = $plan->sort_order;
            $this->features = is_array($plan->features) ? implode("\n", $plan->features) : '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $featuresArray = array_filter(array_map('trim', explode("\n", $this->features)));

        if ($this->editing) {
            $this->plan->update([
                'name' => $this->name,
                'type' => $this->type,
                'billing_cycle' => $this->billingCycle,
                'price' => $this->price,
                'currency' => $this->currency,
                'description' => $this->description,
                'is_active' => $this->isActive,
                'sort_order' => $this->sortOrder,
                'features' => $featuresArray,
            ]);

            activity()->performedOn($this->plan)->log("Updated plan: {$this->name}");
        } else {
            $plan = Plan::create([
                'name' => $this->name,
                'type' => $this->type,
                'billing_cycle' => $this->billingCycle,
                'price' => $this->price,
                'currency' => $this->currency,
                'description' => $this->description,
                'is_active' => $this->isActive,
                'sort_order' => $this->sortOrder,
                'features' => $featuresArray,
            ]);

            activity()->performedOn($plan)->log("Created plan: {$this->name}");
        }

        $this->dispatch('notify', message: $this->editing ? 'Plan updated.' : 'Plan created.', type: 'success');
        $this->redirectRoute('admin.subscriptions.plans');
    }

    public function render()
    {
        return view('subscriptions::livewire.admin.plan-form')->layout('layouts.app');
    }
}
