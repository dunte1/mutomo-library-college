<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public array $settings = [];

    public bool $saved = false;

    public mixed $cardLogo = null;

    public ?string $currentCardLogo = null;

    protected function rules(): array
    {
        return [
            'settings.site_name' => ['required', 'string', 'max:255'],
            'settings.site_description' => ['nullable', 'string', 'max:1000'],
            'settings.library_address' => ['nullable', 'string', 'max:500'],
            'settings.library_phone' => ['nullable', 'string', 'max:50'],
            'settings.library_email' => ['nullable', 'email', 'max:255'],
            'settings.library_website' => ['nullable', 'string', 'max:500'],
            'settings.library_motto' => ['nullable', 'string', 'max:255'],
            'settings.opening_hours' => ['nullable', 'string', 'max:500'],
            'settings.footer_copyright' => ['nullable', 'string', 'max:500'],
            'settings.footer_facebook_url' => ['nullable', 'string', 'max:500'],
            'settings.footer_twitter_url' => ['nullable', 'string', 'max:500'],
            'settings.card_primary_color' => ['nullable', 'string', 'max:7'],
            'settings.card_secondary_color' => ['nullable', 'string', 'max:7'],
            'settings.card_tertiary_color' => ['nullable', 'string', 'max:7'],
            'settings.card_text_color' => ['nullable', 'string', 'max:7'],
            'settings.card_accent_color' => ['nullable', 'string', 'max:7'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        $service = app(SettingsService::class);
        $display = $service->getDisplaySettings();
        $footer = $service->getFooterSettings();
        $cardBranding = $service->getCardBrandingSettings();
        $this->settings = [
            'site_name' => $display['site_name'],
            'site_description' => $display['site_description'],
            'library_address' => $display['library_address'],
            'library_phone' => $display['library_phone'],
            'library_email' => $display['library_email'],
            'library_website' => $display['library_website'],
            'library_motto' => $display['library_motto'],
            'opening_hours' => $display['opening_hours'],
            'footer_copyright' => $footer['footer_copyright'],
            'footer_facebook_url' => $footer['footer_facebook_url'],
            'footer_twitter_url' => $footer['footer_twitter_url'],
            'card_primary_color' => $cardBranding['card_primary_color'],
            'card_secondary_color' => $cardBranding['card_secondary_color'],
            'card_tertiary_color' => $cardBranding['card_tertiary_color'],
            'card_text_color' => $cardBranding['card_text_color'],
            'card_accent_color' => $cardBranding['card_accent_color'],
        ];
        $this->currentCardLogo = $cardBranding['card_logo'] ?: null;
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('general', $this->settings);

        $this->saved = true;
        session()->flash('success', 'General settings saved successfully.');
    }

    public function saveCardLogo(): void
    {
        $this->validate([
            'cardLogo' => 'nullable|image|max:2048',
        ]);

        if ($this->cardLogo) {
            $path = $this->cardLogo->store('settings/card-logo', 'public');
            app(SettingsService::class)->updateSettings('general', ['card_logo' => $path]);
            $this->currentCardLogo = $path;
            $this->cardLogo = null;
            session()->flash('success', 'Card logo uploaded successfully.');
        }
    }

    public function removeCardLogo(): void
    {
        app(SettingsService::class)->updateSettings('general', ['card_logo' => '']);
        $this->currentCardLogo = null;
        session()->flash('success', 'Card logo removed.');
    }

    public function render()
    {
        return view('settings::livewire.general-settings');
    }
}
