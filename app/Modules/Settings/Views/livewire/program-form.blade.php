@section('title', 'Program Form')
<div>
    <x-header :title="$isEditing ? 'Edit Program' : 'Add Program'" :subtitle="$isEditing ? 'Update program details' : 'Create a new academic program'">
        <x-slot:actions>
            <a href="{{ route('settings.programs') }}" wire:navigate class="btn-outline btn-sm">
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
                <h3 class="font-semibold text-surface-900 dark:text-white">Program Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Program Name *</label>
                        <input type="text" wire:model="name" class="input-field" placeholder="e.g. Diploma in Nursing">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Code *</label>
                        <input type="text" wire:model="code" class="input-field" placeholder="e.g. DIPNUR">
                        @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Department *</label>
                        <select wire:model="department_id" class="input-field">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Duration (Years) *</label>
                        <input type="number" wire:model="duration_years" min="1" max="10" class="input-field">
                        @error('duration_years') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="description" rows="3" class="input-field" placeholder="Program description..."></textarea>
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
            <a href="{{ route('settings.programs') }}" wire:navigate class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Program' : 'Create Program' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
