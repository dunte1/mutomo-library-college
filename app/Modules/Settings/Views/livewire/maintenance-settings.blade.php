@section('title', 'Maintenance')
<div>
    <x-slot name="header">Maintenance Mode</x-slot>
    <x-slot name="subtitle">Enable or disable maintenance mode for the entire system</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            @if($maintenanceMode)
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-amber-800 dark:text-amber-200">Maintenance mode is currently active</p>
                            <p class="text-sm text-amber-600 dark:text-amber-400">The system is not accessible to regular users.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-emerald-800 dark:text-emerald-200">System is live</p>
                            <p class="text-sm text-emerald-600 dark:text-emerald-400">All services are running normally.</p>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="{{ $maintenanceMode ? 'disable' : 'enable' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Secret / Bypass Token</label>
                        <input type="text" wire:model="secret" class="input-field" placeholder="/secret-token" {{ $maintenanceMode ? 'disabled' : '' }}>
                        @error("secret") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-surface-400 mt-1">Authorized users can bypass maintenance using /secret-token</p>
                    </div>
                    <div>
                        <label class="label">Maintenance Message</label>
                        <input type="text" wire:model="message" class="input-field" placeholder="Site is under maintenance..." {{ $maintenanceMode ? 'disabled' : '' }}>
                        <p class="text-xs text-surface-400 mt-1">Shown to users while maintenance is active</p>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    @if($maintenanceMode)
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Disable Maintenance Mode</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    @else
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Enable Maintenance Mode</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
