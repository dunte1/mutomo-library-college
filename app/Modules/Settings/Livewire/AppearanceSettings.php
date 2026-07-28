<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use App\Modules\Settings\Models\Setting;
use Livewire\WithFileUploads;

class AppearanceSettings extends Component
{
    use WithFileUploads;

    public array $settings = [];

    public $siteLogo;

    public $favicon;

    public $documentLogo;

    public bool $saved = false;

    public ?string $currentLogoUrl = null;

    public ?string $currentFaviconUrl = null;

    public ?string $currentDocumentLogoUrl = null;

    protected $rules = [
        'settings.theme' => 'required|string|in:light,dark,auto',
        'settings.primary_color' => 'required|string|max:7',
        'settings.sidebar_collapsed' => 'boolean',
        'settings.show_analytics' => 'boolean',
        'settings.document_header_text' => 'nullable|string|max:255',
        'settings.document_footer_text' => 'nullable|string|max:255',
        'settings.document_primary_color' => 'nullable|string|max:7',
        'settings.document_show_verification_stamp' => 'boolean',
        'settings.document_show_qr_code' => 'boolean',
        'settings.document_footer_disclaimer' => 'nullable|string|max:500',
        'siteLogo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        'favicon' => 'nullable|image|mimes:ico,png,svg|max:512',
        'documentLogo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        $service = app(SettingsService::class);
        $branding = $service->getBrandingSettings();

        $this->settings = [
            'theme' => $service->cached('theme', 'light'),
            'primary_color' => $service->cached('primary_color', '#1E4FA3'),
            'sidebar_collapsed' => (bool) $service->cached('sidebar_collapsed', false),
            'show_analytics' => (bool) $service->cached('show_analytics', true),
            'document_header_text' => $branding['document_header_text'],
            'document_footer_text' => $branding['document_footer_text'],
            'document_primary_color' => $branding['document_primary_color'],
            'document_show_verification_stamp' => (bool) $branding['document_show_verification_stamp'],
            'document_show_qr_code' => (bool) $branding['document_show_qr_code'],
            'document_footer_disclaimer' => $branding['document_footer_disclaimer'],
        ];

        $logo = $service->cached('site_logo');
        if ($logo) {
            $this->currentLogoUrl = url('storage/'.$logo);
        }

        $favicon = $service->cached('favicon');
        if ($favicon) {
            $this->currentFaviconUrl = url('storage/'.$favicon);
        }

        $docLogo = $service->cached('document_logo');
        if ($docLogo) {
            $this->currentDocumentLogoUrl = url('storage/'.$docLogo);
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->settings;

        if ($this->siteLogo) {
            $path = $this->siteLogo->store('settings', 'public');
            $data['site_logo'] = $path;
            $this->currentLogoUrl = url('storage/'.$path);
            $this->siteLogo = null;
        }

        if ($this->favicon) {
            $path = $this->favicon->store('settings', 'public');
            $data['favicon'] = $path;
            $this->currentFaviconUrl = url('storage/'.$path);
            $this->favicon = null;
        }

        if ($this->documentLogo) {
            $path = $this->documentLogo->store('settings', 'public');
            $data['document_logo'] = $path;
            $this->currentDocumentLogoUrl = url('storage/'.$path);
            $this->documentLogo = null;
        }

        app(SettingsService::class)->updateSettings('appearance', $data);

        $this->saved = true;
    }

    public function removeDocumentLogo(): void
    {
        $logo = Setting::value('document_logo');
        if ($logo) {
            Storage::disk('public')->delete($logo);
        }
        Setting::where('key', 'document_logo')->delete();
        app(SettingsService::class)->clearGroupCache('appearance');
        $this->currentDocumentLogoUrl = null;
        $this->saved = true;
    }

    public function removeLogo(): void
    {
        $logo = Setting::value('site_logo');
        if ($logo) {
            Storage::disk('public')->delete($logo);
        }
        Setting::where('key', 'site_logo')->delete();
        app(SettingsService::class)->clearGroupCache('appearance');
        $this->currentLogoUrl = null;
        $this->saved = true;
    }

    public function removeFavicon(): void
    {
        $favicon = Setting::value('favicon');
        if ($favicon) {
            Storage::disk('public')->delete($favicon);
        }
        Setting::where('key', 'favicon')->delete();
        app(SettingsService::class)->clearGroupCache('appearance');
        $this->currentFaviconUrl = null;
        $this->saved = true;
    }

    public function render()
    {
        return view('settings::livewire.appearance-settings');
    }
}
