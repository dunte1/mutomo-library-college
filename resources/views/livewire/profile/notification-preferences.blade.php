<?php

use Livewire\Volt\Component;

new class extends Component
{
    public bool $emailNotifications = true;
    public bool $dueDateReminders = true;
    public bool $overdueAlerts = true;
    public bool $newArrivalAlerts = false;
    public bool $fineNotifications = true;
    public bool $holdAvailableNotifications = true;

    public function mount(): void
    {
        $settings = auth()->user()->notification_preferences ?? [];
        $this->emailNotifications = $settings['email'] ?? true;
        $this->dueDateReminders = $settings['due_date'] ?? true;
        $this->overdueAlerts = $settings['overdue'] ?? true;
        $this->newArrivalAlerts = $settings['new_arrivals'] ?? false;
        $this->fineNotifications = $settings['fines'] ?? true;
        $this->holdAvailableNotifications = $settings['holds'] ?? true;
    }

    public function save(): void
    {
        auth()->user()->update([
            'notification_preferences' => [
                'email' => $this->emailNotifications,
                'due_date' => $this->dueDateReminders,
                'overdue' => $this->overdueAlerts,
                'new_arrivals' => $this->newArrivalAlerts,
                'fines' => $this->fineNotifications,
                'holds' => $this->holdAvailableNotifications,
            ],
        ]);

        $this->dispatch('notify', message: 'Notification preferences saved.', type: 'success');
    }
}; ?>

<section class="card">
    <div class="card-header">
        <h3 class="font-semibold text-surface-900 dark:text-white">Notification Preferences</h3>
    </div>
    <div class="card-body">
        <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">Choose what notifications you'd like to receive.</p>

        <form wire:submit="save" class="space-y-4">
            <div class="space-y-4">
                <label class="inline-flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-800/50">
                    <input type="checkbox" wire:model="emailNotifications" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                    <div>
                        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Email Notifications</span>
                        <p class="text-xs text-surface-400">Receive notifications via email</p>
                    </div>
                </label>

                <label class="inline-flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-800/50">
                    <input type="checkbox" wire:model="dueDateReminders" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                    <div>
                        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Due Date Reminders</span>
                        <p class="text-xs text-surface-400">Get reminded before borrowed items are due</p>
                    </div>
                </label>

                <label class="inline-flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-800/50">
                    <input type="checkbox" wire:model="overdueAlerts" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                    <div>
                        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Overdue Alerts</span>
                        <p class="text-xs text-surface-400">Get notified when items become overdue</p>
                    </div>
                </label>

                <label class="inline-flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-800/50">
                    <input type="checkbox" wire:model="newArrivalAlerts" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                    <div>
                        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">New Arrival Alerts</span>
                        <p class="text-xs text-surface-400">Be notified about new books and digital assets</p>
                    </div>
                </label>

                <label class="inline-flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-800/50">
                    <input type="checkbox" wire:model="fineNotifications" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                    <div>
                        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Fine Notifications</span>
                        <p class="text-xs text-surface-400">Get notified about fines and payments</p>
                    </div>
                </label>

                <label class="inline-flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-800/50">
                    <input type="checkbox" wire:model="holdAvailableNotifications" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                    <div>
                        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Hold Availability</span>
                        <p class="text-xs text-surface-400">Get notified when a reserved book becomes available</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Save Preferences</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</section>
