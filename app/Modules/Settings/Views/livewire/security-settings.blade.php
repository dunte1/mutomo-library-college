@section('title', 'Security')
<div>
    <x-slot name="header">Security Settings</x-slot>
    <x-slot name="subtitle">Configure password policies, authentication and session rules</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div>
                    <h3 class="font-semibold text-surface-900 dark:text-white mb-3">Password Policy</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label">Minimum Password Length</label>
                            <input type="number" wire:model="settings.min_password_length" class="input-field" min="6" max="128">
                            @error("settings.min_password_length") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3 pt-6">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settings.require_uppercase" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <span class="text-sm text-surface-700 dark:text-surface-300">Require uppercase letter</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settings.require_numbers" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <span class="text-sm text-surface-700 dark:text-surface-300">Require number</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settings.require_special_chars" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <span class="text-sm text-surface-700 dark:text-surface-300">Require special character</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="border-t border-surface-200 dark:border-surface-700 pt-6">
                    <h3 class="font-semibold text-surface-900 dark:text-white mb-3">Login & Session</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label">Max Login Attempts</label>
                            <input type="number" wire:model="settings.max_login_attempts" class="input-field" min="1" max="50">
                            @error("settings.max_login_attempts") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label">Session Timeout (minutes)</label>
                            <input type="number" wire:model="settings.session_timeout" class="input-field" min="5" max="1440">
                            @error("settings.session_timeout") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settings.two_factor_required" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <span class="text-sm text-surface-700 dark:text-surface-300">Require two-factor authentication</span>
                            </label>
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
