@section('title', 'Messaging Preferences')
<div>
    <x-slot name="header">Messaging Preferences</x-slot>
    <x-slot name="subtitle">Control how you receive notifications</x-slot>

    <div class="max-w-2xl">
        <div class="card p-6">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <h3 class="font-medium text-surface-900 dark:text-white mb-4">Notification Channels</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Choose which channels to receive message notifications on.</p>

                    <div class="space-y-4">
                        <label class="flex items-start gap-3 p-4 rounded-lg bg-surface-50 dark:bg-surface-800 cursor-pointer hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                            <input type="checkbox" wire:model="inAppNotifications"
                                class="mt-0.5 rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <div>
                                <p class="font-medium text-surface-900 dark:text-white">In-App Notifications</p>
                                <p class="text-sm text-surface-500 dark:text-surface-400">Notifications within the application (bell icon)</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-lg bg-surface-50 dark:bg-surface-800 cursor-pointer hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                            <input type="checkbox" wire:model="emailNotifications"
                                class="mt-0.5 rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <div>
                                <p class="font-medium text-surface-900 dark:text-white">Email Notifications</p>
                                <p class="text-sm text-surface-500 dark:text-surface-400">Receive message notifications via email</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-lg bg-surface-50 dark:bg-surface-800 cursor-pointer hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                            <input type="checkbox" wire:model="pushNotifications"
                                class="mt-0.5 rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <div>
                                <p class="font-medium text-surface-900 dark:text-white">Push Notifications</p>
                                <p class="text-sm text-surface-500 dark:text-surface-400">Browser push notifications</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-lg bg-surface-50 dark:bg-surface-800 cursor-pointer hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                            <input type="checkbox" wire:model="smsNotifications"
                                class="mt-0.5 rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <div>
                                <p class="font-medium text-surface-900 dark:text-white">SMS Notifications</p>
                                <p class="text-sm text-surface-500 dark:text-surface-400">Text message notifications to your phone</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Preferences</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
