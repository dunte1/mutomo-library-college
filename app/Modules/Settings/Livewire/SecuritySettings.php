<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class SecuritySettings extends Component
{
    public array $settings = [];

    protected $rules = [
        'settings.min_password_length' => 'required|integer|min:6|max:128',
        'settings.require_uppercase' => 'boolean',
        'settings.require_numbers' => 'boolean',
        'settings.require_special_chars' => 'boolean',
        'settings.max_login_attempts' => 'required|integer|min:1|max:50',
        'settings.session_timeout' => 'required|integer|min:5|max:1440',
        'settings.two_factor_required' => 'boolean',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        $this->settings = app(SettingsService::class)->getSecuritySettings();
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('security', $this->settings);

        session()->flash('success', 'Security settings saved successfully.');
    }

    public function render()
    {
        return view('settings::livewire.security-settings');
    }
}
