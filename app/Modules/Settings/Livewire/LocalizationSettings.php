<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class LocalizationSettings extends Component
{
    public array $settings = [];

    protected $rules = [
        'settings.default_language' => 'required|string|size:2',
        'settings.default_timezone' => 'required|string|timezone',
        'settings.date_format' => 'required|string|max:50',
        'settings.time_format' => 'required|string|max:50',
        'settings.currency' => 'required|string|size:3',
        'settings.first_day_of_week' => 'required|string|in:monday,sunday,saturday',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        $this->settings = app(SettingsService::class)->getLocalizationSettings();
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('localization', $this->settings);

        session()->flash('success', 'Localization settings saved successfully.');
    }

    public function render()
    {
        return view('settings::livewire.localization-settings');
    }
}
