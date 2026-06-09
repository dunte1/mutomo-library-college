<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use App\Modules\Subscriptions\Models\Plan;
use Livewire\Component;

class SubscriptionSettings extends Component
{
    public array $settings = [];
    public bool $saved = false;

    protected function rules(): array
    {
        return [
            'settings.individual_monthly_fee' => ['required', 'numeric', 'min:0'],
            'settings.individual_yearly_fee' => ['required', 'numeric', 'min:0'],
            'settings.school_monthly_fee' => ['required', 'numeric', 'min:0'],
            'settings.school_yearly_fee' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function mount(): void
    {
        $this->settings = [
            'individual_monthly_fee' => (float) Setting::value('individual_monthly_fee', 500),
            'individual_yearly_fee' => (float) Setting::value('individual_yearly_fee', 5000),
            'school_monthly_fee' => (float) Setting::value('school_monthly_fee', 2000),
            'school_yearly_fee' => (float) Setting::value('school_yearly_fee', 20000),
        ];
    }

    public function save(): void
    {
        $this->validate();

        foreach ($this->settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'subscriptions'],
                [
                    'value' => (string) $value,
                    'type' => 'float',
                    'description' => match ($key) {
                        'individual_monthly_fee' => 'Individual Monthly Subscription Fee',
                        'individual_yearly_fee' => 'Individual Yearly Subscription Fee',
                        'school_monthly_fee' => 'School Monthly Subscription Fee',
                        'school_yearly_fee' => 'School Yearly Subscription Fee',
                        default => '',
                    },
                ]
            );
        }

        Plan::updateOrCreate(['slug' => 'individual-monthly'], [
            'name' => 'Individual Monthly',
            'type' => 'individual',
            'billing_cycle' => 'monthly',
            'price' => $this->settings['individual_monthly_fee'],
            'currency' => 'KES',
            'is_active' => $this->settings['individual_monthly_fee'] > 0,
        ]);

        Plan::updateOrCreate(['slug' => 'individual-yearly'], [
            'name' => 'Individual Yearly',
            'type' => 'individual',
            'billing_cycle' => 'yearly',
            'price' => $this->settings['individual_yearly_fee'],
            'currency' => 'KES',
            'is_active' => $this->settings['individual_yearly_fee'] > 0,
        ]);

        Plan::updateOrCreate(['slug' => 'school-monthly'], [
            'name' => 'School Monthly',
            'type' => 'school',
            'billing_cycle' => 'monthly',
            'price' => $this->settings['school_monthly_fee'],
            'currency' => 'KES',
            'is_active' => $this->settings['school_monthly_fee'] > 0,
        ]);

        Plan::updateOrCreate(['slug' => 'school-yearly'], [
            'name' => 'School Yearly',
            'type' => 'school',
            'billing_cycle' => 'yearly',
            'price' => $this->settings['school_yearly_fee'],
            'currency' => 'KES',
            'is_active' => $this->settings['school_yearly_fee'] > 0,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['settings' => $this->settings])
            ->log('Updated subscription pricing settings');

        $this->saved = true;
        session()->flash('success', 'Subscription pricing saved successfully. Plans have been updated.');
    }

    public function render()
    {
        return view('settings::livewire.subscription-settings');
    }
}
