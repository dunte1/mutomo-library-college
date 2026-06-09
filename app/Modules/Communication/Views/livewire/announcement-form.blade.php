@section('title', 'Announcement Form')
<div>
    <x-header :title="$isEditing ? 'Edit Announcement' : 'Add Announcement'" :subtitle="$isEditing ? 'Update announcement details' : 'Create a new announcement'">
        <x-slot:actions>
            <a href="{{ route('communication.announcements.index') }}" wire:navigate class="btn-outline btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                Back
            </a>
        </x-slot:actions>
    </x-header>

    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Announcement Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label">Title *</label>
                        <input type="text" wire:model="title" class="input-field" placeholder="Announcement title">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Type *</label>
                        <select wire:model="type" class="input-field">
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="important">Important</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Status *</label>
                        <select wire:model="status" class="input-field">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Published At</label>
                        <input type="datetime-local" wire:model="published_at" class="input-field">
                        @error('published_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Expires At</label>
                        <input type="datetime-local" wire:model="expires_at" class="input-field">
                        @error('expires_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Content *</label>
                        <textarea wire:model="content" rows="6" class="input-field" placeholder="Announcement content..."></textarea>
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('communication.announcements.index') }}" wire:navigate class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Announcement' : 'Create Announcement' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
