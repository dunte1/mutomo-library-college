@section('title', 'Backup')
<div>
    <x-slot name="header">Backup Settings</x-slot>
    <x-slot name="subtitle">Configure automatic backups and storage preferences</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Backup Frequency</label>
                        <select wire:model="settings.backup_frequency" class="input-field">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        @error("settings.backup_frequency") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Backup Location</label>
                        <select wire:model="settings.backup_location" class="input-field">
                            <option value="local">Local Storage</option>
                            <option value="s3">Amazon S3</option>
                            <option value="dropbox">Dropbox</option>
                            <option value="gcs">Google Cloud Storage</option>
                        </select>
                        @error("settings.backup_location") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Retention (days)</label>
                        <input type="number" wire:model="settings.backup_retention_days" class="input-field" min="1" max="365">
                        @error("settings.backup_retention_days") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="settings.auto_backup" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Enable automatic backups</span>
                        </label>
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

    {{-- Manual Backup --}}
    <div class="card mt-6">
        <div class="card-header">
            <h3 class="font-semibold text-surface-900 dark:text-white">Manual Backup</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Create a manual database backup right now.</p>

            @if($lastBackupDate)
                <div class="flex items-center gap-4 mb-4 p-3 rounded-xl bg-surface-50 dark:bg-surface-800/50">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-surface-900 dark:text-white">Latest Backup</p>
                        <p class="text-xs text-surface-500">{{ $lastBackupDate }} @if($lastBackupSize)({{ $lastBackupSize }})@endif</p>
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-3">
                <button wire:click="createBackup" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove>
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Create Backup Now
                    </span>
                    <span wire:loading>
                        <svg class="animate-spin w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Creating Backup...
                    </span>
                </button>
            </div>

            @if($backupResult)
                <div class="mt-4 p-3 rounded-xl {{ $backupSuccess ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' }} text-sm">
                    {{ $backupResult }}
                </div>
            @endif
        </div>
    </div>
</div>
