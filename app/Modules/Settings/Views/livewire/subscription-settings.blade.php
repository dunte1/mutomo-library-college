<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Subscription &amp; Billing Settings</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Configure subscription pricing plans. Changes take effect immediately.</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-900 dark:text-white">Individual Plans</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Individual Monthly Fee (KES)</label>
                    <input type="number" wire:model="settings.individual_monthly_fee" step="0.01" min="0" class="mt-1 block w-full input-field">
                    @error('settings.individual_monthly_fee') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Individual Yearly Fee (KES)</label>
                    <input type="number" wire:model="settings.individual_yearly_fee" step="0.01" min="0" class="mt-1 block w-full input-field">
                    @error('settings.individual_yearly_fee') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-900 dark:text-white">School Plans</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">School Monthly Fee (KES)</label>
                    <input type="number" wire:model="settings.school_monthly_fee" step="0.01" min="0" class="mt-1 block w-full input-field">
                    @error('settings.school_monthly_fee') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">School Yearly Fee (KES)</label>
                    <input type="number" wire:model="settings.school_yearly_fee" step="0.01" min="0" class="mt-1 block w-full input-field">
                    @error('settings.school_yearly_fee') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="btn-primary">Save Settings</button>
            @if($saved)
                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Settings saved successfully!</span>
            @endif
        </div>
    </form>
</div>
