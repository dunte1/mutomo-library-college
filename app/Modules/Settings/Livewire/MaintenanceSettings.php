<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class MaintenanceSettings extends Component
{
    public bool $maintenanceMode = false;
    public string $secret = '';
    public string $message = '';
    public ?string $lastCommandResult = null;

    public function mount(): void
    {
        $this->maintenanceMode = app()->isDownForMaintenance();
        $this->secret = app(SettingsService::class)->cached('maintenance_secret', '');
        $this->message = app(SettingsService::class)->cached('maintenance_message', 'We are currently performing scheduled maintenance. Please check back shortly.');
    }

    public function enable(): void
    {
        $this->validate([
            'secret' => ['required', 'string', 'min:4', 'max:255'],
        ]);

        $params = ['--secret' => $this->secret];

        if (!empty($this->message)) {
            $params['--retry'] = $this->message;
        }

        Artisan::call('down', $params);

        app(SettingsService::class)->updateSettings('maintenance', [
            'maintenance_secret' => $this->secret,
            'maintenance_message' => $this->message,
        ]);

        $this->maintenanceMode = true;
        $this->lastCommandResult = Artisan::output();
        $this->dispatch('notify', message: 'Maintenance mode has been enabled.', type: 'success');
    }

    public function disable(): void
    {
        Artisan::call('up');

        $this->maintenanceMode = false;
        $this->lastCommandResult = Artisan::output();
        $this->dispatch('notify', message: 'Maintenance mode has been disabled.', type: 'success');
    }

    public function render()
    {
        return view('settings::livewire.maintenance-settings');
    }
}
