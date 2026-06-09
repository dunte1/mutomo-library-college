<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Digital Categories</h1>
            <p class="page-subtitle">Manage digital asset categories</p>
        </div>
        @can('manage-digital-categories')
        <button wire:click="create" class="btn-sm btn-primary">Add Category</button>
        @endcan
    </div>

    @if($showForm)
    <div class="card mb-6 border-primary-200 dark:border-primary-800">
        <div class="card-body">
            <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Category' : 'New Category' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="label">Name</label>
                    <input type="text" wire:model="name" class="input w-full" placeholder="e.g., E-books, Journals, Videos">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea wire:model="description" class="input w-full" rows="3" placeholder="Optional description"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="isActive" class="rounded border-surface-300 text-primary-600" id="isActive">
                    <label for="isActive" class="text-sm">Active</label>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-sm btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
                    <button type="button" wire:click="cancel" class="btn-sm btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto table-mobile-cards">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Assets</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td class="font-medium">{{ $category->name }}</td>
                            <td class="text-sm text-surface-500 max-w-xs truncate">{{ $category->description ?? '—' }}</td>
                            <td class="text-sm">{{ $category->digital_assets_count }}</td>
                            <td>
                                @if($category->is_active)
                                <span class="badge badge-success">Active</span>
                                @else
                                <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    @can('manage-digital-categories')
                                    <button wire:click="edit({{ $category->id }})" class="btn-sm btn-secondary">Edit</button>
                                    <button wire:click="toggleActive({{ $category->id }})" class="btn-sm {{ $category->is_active ? 'btn-warning' : 'btn-success' }}">
                                        {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-surface-400">No categories found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
