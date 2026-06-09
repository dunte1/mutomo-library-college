@section('title', 'Notification Settings')
<div>
    <x-slot name="header">Notification Settings</x-slot>
    <x-slot name="subtitle">Configure alerts, reminders and notification channels</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-surface-900 dark:text-white mb-3">Notification Channels</h3>
                        <div class="space-y-3">
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="settings.email_notifications" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <div>
                                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Email Notifications</span>
                                    <p class="text-xs text-surface-400">Send notifications via email</p>
                                </div>
                            </label>

                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="settings.sms_notifications" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <div>
                                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">SMS Notifications</span>
                                    <p class="text-xs text-surface-400">Send notifications via SMS</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-surface-200 dark:border-surface-700 pt-4">
                        <h3 class="font-semibold text-surface-900 dark:text-white mb-3">Alert Types</h3>
                        <div class="space-y-3">
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="settings.due_date_reminders" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <div>
                                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Due Date Reminders</span>
                                    <p class="text-xs text-surface-400">Remind members before items are due</p>
                                </div>
                            </label>

                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="settings.overdue_alerts" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <div>
                                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Overdue Alerts</span>
                                    <p class="text-xs text-surface-400">Alert when items become overdue</p>
                                </div>
                            </label>

                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="settings.new_arrival_alerts" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <div>
                                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">New Arrival Alerts</span>
                                    <p class="text-xs text-surface-400">Notify about new books and digital assets</p>
                                </div>
                            </label>

                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="settings.fine_notifications" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <div>
                                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Fine Notifications</span>
                                    <p class="text-xs text-surface-400">Notify members about fines and payments</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-surface-200 dark:border-surface-700 pt-4">
                        <h3 class="font-semibold text-surface-900 dark:text-white mb-3">Reminder Timing</h3>
                        <div>
                            <label class="label">Remind (days before due)</label>
                            <input type="number" wire:model="settings.reminder_days_before" class="input-field w-full md:w-48" min="1" max="30">
                            @error("settings.reminder_days_before") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
