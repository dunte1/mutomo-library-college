@section('title', 'Circulation Rules')
<div>
    <x-slot name="header">Circulation Rules</x-slot>
    <x-slot name="subtitle">Configure borrowing policies, loan periods and fine rates</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Max Borrow Days</label>
                        <input type="number" wire:model="settings.max_borrow_days" class="input-field" min="1" max="365">
                        @error("settings.max_borrow_days") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Max Borrow Items</label>
                        <input type="number" wire:model="settings.max_borrow_items" class="input-field" min="1" max="100">
                        @error("settings.max_borrow_items") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Renewal Days</label>
                        <input type="number" wire:model="settings.renewal_days" class="input-field" min="1" max="365">
                        @error("settings.renewal_days") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Max Renewals</label>
                        <input type="number" wire:model="settings.max_renewals" class="input-field" min="0" max="10">
                        @error("settings.max_renewals") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Fine Per Day (KES)</label>
                        <input type="number" step="0.01" wire:model="settings.fine_per_day" class="input-field" min="0">
                        @error("settings.fine_per_day") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Grace Period (Days)</label>
                        <input type="number" wire:model="settings.grace_period_days" class="input-field" min="0" max="30">
                        @error("settings.grace_period_days") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
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
