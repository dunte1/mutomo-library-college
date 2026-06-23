<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class GeneralSettings extends Component
{
    public array $settings = [];

    public bool $saved = false;

    protected function rules(): array
    {
        return [
            'settings.site_name' => ['required', 'string', 'max:255'],
            'settings.site_description' => ['nullable', 'string', 'max:1000'],
            'settings.library_address' => ['nullable', 'string', 'max:500'],
            'settings.library_phone' => ['nullable', 'string', 'max:50'],
            'settings.library_email' => ['nullable', 'email', 'max:255'],
            'settings.opening_hours' => ['nullable', 'string', 'max:500'],
            'settings.footer_copyright' => ['nullable', 'string', 'max:500'],
            'settings.footer_facebook_url' => ['nullable', 'string', 'max:500'],
            'settings.footer_twitter_url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function mount(): void
    {
        $service = app(SettingsService::class);
        $display = $service->getDisplaySettings();
        $footer = $service->getFooterSettings();
        $this->settings = [
            'site_name' => $display['site_name'],
            'site_description' => $display['site_description'],
            'library_address' => $display['library_address'],
            'library_phone' => $display['library_phone'],
            'library_email' => $display['library_email'],
            'opening_hours' => $display['opening_hours'],
            'footer_copyright' => $footer['footer_copyright'],
            'footer_facebook_url' => $footer['footer_facebook_url'],
            'footer_twitter_url' => $footer['footer_twitter_url'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('general', $this->settings);

        $this->saved = true;
        session()->flash('success', 'General settings saved successfully.');
    }

    public function render()
    {
        return view('settings::livewire.general-settings');
    }
}
