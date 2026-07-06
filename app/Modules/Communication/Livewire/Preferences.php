<?php

namespace App\Modules\Communication\Livewire;

use Livewire\Component;

class Preferences extends Component
{
    public bool $emailNotifications = true;

    public bool $smsNotifications = false;

    public bool $pushNotifications = true;

    public bool $inAppNotifications = true;

    public function mount(): void
    {
        $prefs = auth()->user()->notification_preferences ?? [];

        $this->emailNotifications = $prefs['email'] ?? true;
        $this->smsNotifications = $prefs['sms'] ?? false;
        $this->pushNotifications = $prefs['push'] ?? true;
        $this->inAppNotifications = $prefs['in_app'] ?? true;
    }

    public function save(): void
    {
        auth()->user()->update([
            'notification_preferences' => [
                'email' => $this->emailNotifications,
                'sms' => $this->smsNotifications,
                'push' => $this->pushNotifications,
                'in_app' => $this->inAppNotifications,
            ],
        ]);

        $this->dispatch('notify', message: 'Preferences saved.', type: 'success');
    }

    public function render()
    {
        return view('communication::livewire.preferences')
            ->layout('layouts.app')
            ->title('Messaging Preferences');
    }
}
