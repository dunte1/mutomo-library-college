<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class NotificationSettings extends Component
{
    public array $settings = [];

    protected $rules = [
        'settings.email_notifications' => 'boolean',
        'settings.sms_notifications' => 'boolean',
        'settings.due_date_reminders' => 'boolean',
        'settings.overdue_alerts' => 'boolean',
        'settings.new_arrival_alerts' => 'boolean',
        'settings.fine_notifications' => 'boolean',
        'settings.reminder_days_before' => 'required|integer|min:1|max:30',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        $this->settings = app(SettingsService::class)->getNotificationSettings();
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('notifications', $this->settings);

        session()->flash('success', 'Notification settings saved successfully.');
    }

    public function render()
    {
        return view('settings::livewire.notification-settings');
    }
}
