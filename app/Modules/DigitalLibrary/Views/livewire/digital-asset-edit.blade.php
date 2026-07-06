@section('title', 'Edit Digital Asset')
<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Edit Digital Asset</h2>
        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Update metadata for "{{ $asset->title }}"</p>
    </div>

    <form wire:submit="save" class="card">
        <div class="card-body space-y-6">
            <x-field label="Title" required>
                <x-input wire:model="title" placeholder="Asset title" />
            </x-field>

            <x-field label="Description">
                <textarea wire:model="description" class="input-field" rows="3" placeholder="Brief description..."></textarea>
            </x-field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-field label="Category">
                    <select wire:model="categoryId" class="input-field">
                        <option value="">No Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </x-field>

                <x-field label="Access Level">
                    <select wire:model="accessLevel" class="input-field">
                        <option value="public">Public</option>
                        <option value="restricted">Restricted</option>
                        <option value="premium">Premium</option>
                    </select>
                </x-field>

                <x-field label="Author">
                    <x-input wire:model="author" placeholder="Author name" />
                </x-field>

                <x-field label="Publisher">
                    <x-input wire:model="publisher" placeholder="Publisher name" />
                </x-field>

                <x-field label="Publication Year">
                    <x-input wire:model="publicationYear" type="number" placeholder="e.g. 2024" />
                </x-field>

                <x-field label="Language">
                    <x-input wire:model="language" placeholder="e.g. en" />
                </x-field>
            </div>

            <x-field label="Keywords (comma-separated)">
                <x-input wire:model="keywords" placeholder="e.g. biology, textbook, anatomy" />
            </x-field>

            <div class="flex flex-wrap gap-6">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="allowDownload" class="rounded border-surface-300 text-primary-600">
                    <span class="text-sm">Allow Download</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="allowPrinting" class="rounded border-surface-300 text-primary-600">
                    <span class="text-sm">Allow Printing</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="isActive" class="rounded border-surface-300 text-primary-600">
                    <span class="text-sm">Active</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="isFeatured" class="rounded border-surface-300 text-primary-600">
                    <span class="text-sm">Featured</span>
                </label>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Save Changes</span>
                    <span wire:loading>Saving...</span>
                </button>
                <a href="{{ route('digital-library.show', $asset) }}" wire:navigate class="btn-outline">Cancel</a>
            </div>
        </div>
    </form>
</div>
