<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class DigitalLibrarySettings extends Component
{
    public array $settings = [];

    protected $rules = [
        'settings.max_upload_size' => 'required|integer|min:1024|max:1048576',
        'settings.allowed_file_types' => 'required|string|max:500',
        'settings.auto_approve_uploads' => 'boolean',
        'settings.max_assets_per_user' => 'required|integer|min:1|max:10000',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        $this->settings = app(SettingsService::class)->getDigitalLibrarySettings();
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('digital-library', $this->settings);

        session()->flash('success', 'Digital library settings saved successfully.');
    }

    public function render()
    {
        return view('settings::livewire.digital-library-settings');
    }
}
