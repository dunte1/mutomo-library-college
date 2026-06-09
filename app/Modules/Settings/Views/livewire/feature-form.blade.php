@section('title', 'Feature Form')
<div>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('settings.features') }}" wire:navigate class="btn-ghost p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">
                    {{ $isEditing ? 'Edit Feature' : 'Add Feature' }}
                </h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                    {{ $isEditing ? 'Update feature card details' : 'Create a new feature card' }}
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Feature Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Title *</label>
                        <input type="text" wire:model="title" class="input-field" placeholder="e.g. Comprehensive Catalog">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Icon</label>
                        <input type="text" wire:model="icon" class="input-field" placeholder="e.g. 📚 or book" maxlength="50">
                        <p class="text-xs text-surface-400 mt-1">Emoji or short name for the icon</p>
                        @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Sort Order</label>
                        <input type="number" wire:model="sort_order" class="input-field" min="0" max="999">
                        @error('sort_order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2 mt-6">
                            <input type="checkbox" wire:model="is_active" class="rounded border-surface-300">
                            <span class="text-sm font-medium text-surface-900 dark:text-white">Active</span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description *</label>
                        <textarea wire:model="description" rows="4" class="input-field" placeholder="Describe this feature..."></textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('settings.features') }}" wire:navigate class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Feature' : 'Create Feature' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
