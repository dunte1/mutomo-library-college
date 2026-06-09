@section('title', 'Auth Carousel')
<div>
    <x-slot name="header">Auth Page Carousel</x-slot>
    <x-slot name="subtitle">Manage carousel images displayed on login, registration, and other auth pages</x-slot>

    @if ($saved)
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm">
            Changes saved successfully.
        </div>
    @endif

    <div class="card mb-6">
        <div class="card-body">
            <form wire:submit="add">
                <label class="label">Add New Image</label>
                <p class="text-xs text-surface-400 mb-3">Recommended size: 1920x1080px or similar landscape ratio. Max 5MB.</p>
                <div class="flex items-center gap-3">
                    <input type="file" wire:model="newImage" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-300">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Upload</span>
                        <span wire:loading>Uploading...</span>
                    </button>
                </div>
                @error('newImage') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                <div wire:loading wire:target="newImage" class="mt-2 text-sm text-primary-600">Uploading image...</div>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($images as $image)
            <div class="card">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-40 h-24 rounded-lg overflow-hidden bg-surface-100 dark:bg-surface-800 border border-surface-200 dark:border-surface-700">
                            <img src="{{ asset('storage/' . $image['image_path']) }}" alt="{{ $image['title'] ?? 'Carousel image' }}"
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            @if($editId === $image['id'])
                                <div class="space-y-2">
                                    <input type="text" wire:model="editTitle" class="input-field" placeholder="Title (optional)">
                                    @error('editTitle') <p class="text-xs text-accent-600">{{ $message }}</p> @enderror
                                    <input type="text" wire:model="editSubtitle" class="input-field" placeholder="Subtitle (optional)">
                                    @error('editSubtitle') <p class="text-xs text-accent-600">{{ $message }}</p> @enderror
                                    <div class="flex gap-2">
                                        <button wire:click="update" class="btn-primary btn-sm">Save</button>
                                        <button wire:click="cancelEdit" class="btn-secondary btn-sm">Cancel</button>
                                    </div>
                                </div>
                            @else
                                <h3 class="font-semibold text-surface-900 dark:text-white truncate">{{ $image['title'] ?: 'Untitled' }}</h3>
                                <p class="text-sm text-surface-500 dark:text-surface-400 truncate">{{ $image['subtitle'] ?: 'No subtitle' }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs text-surface-400">Order: {{ $image['sort_order'] }}</span>
                                    <span class="text-xs px-1.5 py-0.5 rounded-full {{ $image['is_active'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-surface-100 text-surface-500 dark:bg-surface-800 dark:text-surface-400' }}">
                                        {{ $image['is_active'] ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button wire:click="edit({{ $image['id'] }})" class="p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400 hover:text-primary-600"
                                    title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="toggleActive({{ $image['id'] }})" class="p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400 hover:text-amber-600"
                                    title="Toggle active">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <button wire:click="moveUp({{ $image['id'] }})" class="p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400 hover:text-primary-600"
                                    title="Move up">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                            <button wire:click="moveDown({{ $image['id'] }})" class="p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400 hover:text-primary-600"
                                    title="Move down">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <button wire:click="delete({{ $image['id'] }})" wire:confirm="Delete this carousel image?" class="p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400 hover:text-accent-600"
                                    title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-8 text-surface-400">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p>No carousel images yet. Upload one above.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
