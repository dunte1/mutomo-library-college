@section('title', 'Publisher Form')
<div>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('catalog.publishers') }}" wire:navigate class="btn-ghost p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">
                    {{ $isEditing ? 'Edit Publisher' : 'Add Publisher' }}
                </h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                    {{ $isEditing ? 'Update publisher details' : 'Create a new publisher' }}
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Publisher Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Publisher Name *</label>
                        <input type="text" wire:model="name" class="input-field" placeholder="e.g. O'Reilly Media">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" wire:model="email" class="input-field" placeholder="contact@publisher.com">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model="phone" class="input-field" placeholder="+1 (555) 123-4567">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Website</label>
                        <input type="url" wire:model="website" class="input-field" placeholder="https://example.com">
                        @error('website') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Address</label>
                        <textarea wire:model="address" rows="2" class="input-field" placeholder="Publisher address..."></textarea>
                        @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="description" rows="3" class="input-field" placeholder="Publisher description..."></textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="is_active" class="rounded border-surface-300">
                            <span class="text-sm font-medium text-surface-900 dark:text-white">Active</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('catalog.publishers') }}" wire:navigate class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Publisher' : 'Create Publisher' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
