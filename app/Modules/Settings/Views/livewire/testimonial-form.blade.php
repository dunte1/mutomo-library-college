@section('title', 'Testimonial Form')
<div>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('settings.testimonials') }}" wire:navigate class="btn-ghost p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">
                    {{ $isEditing ? 'Edit Testimonial' : 'Add Testimonial' }}
                </h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                    {{ $isEditing ? 'Update testimonial details' : 'Create a new testimonial' }}
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Testimonial Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Author Name *</label>
                        <input type="text" wire:model="author_name" class="input-field" placeholder="e.g. Jane Doe">
                        @error('author_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Author Role</label>
                        <input type="text" wire:model="author_role" class="input-field" placeholder="e.g. Nursing Student">
                        @error('author_role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Rating</label>
                        <select wire:model="rating" class="input-field">
                            <option value="">No rating</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                        @error('rating') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select wire:model="status" class="input-field">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <label class="label">Testimonial Content *</label>
                        <textarea wire:model="content" rows="4" class="input-field" placeholder="What did this person say?"></textarea>
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('settings.testimonials') }}" wire:navigate class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Testimonial' : 'Create Testimonial' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
