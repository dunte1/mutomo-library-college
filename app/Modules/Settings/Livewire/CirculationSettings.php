<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class CirculationSettings extends Component
{
    public array $settings = [];

    protected $rules = [
        'settings.max_borrow_days' => 'required|integer|min:1|max:365',
        'settings.max_borrow_items' => 'required|integer|min:1|max:100',
        'settings.renewal_days' => 'required|integer|min:1|max:365',
        'settings.max_renewals' => 'required|integer|min:0|max:10',
        'settings.fine_per_day' => 'required|numeric|min:0',
        'settings.grace_period_days' => 'required|integer|min:0|max:30',
    ];

    public function mount(): void
    {
        $this->settings = app(SettingsService::class)->getCirculationRules();
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('circulation', $this->settings);

        session()->flash('success', 'Circulation rules saved successfully.');
    }

    public function render()
    {
        return view('settings::livewire.circulation-settings');
    }
}
