@section('title', 'Why Choose Us')
<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Why Choose Us</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Manage landing page value proposition cards</p>
            </div>
            <a href="{{ route('settings.why-choose-us.create') }}" wire:navigate class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
            </a>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search items..."
                    class="input-field pl-9">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header w-16">Order</th>
                        <th class="table-header">Icon</th>
                        <th class="table-header">Title</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Status</th>
                        <th class="table-header w-40">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="table-cell">
                                <div class="flex items-center gap-1">
                                    <button wire:click="moveUp({{ $item->id }})" class="p-1 hover:text-primary-600 transition-colors" title="Move up">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <span class="text-sm font-mono text-surface-500 w-6 text-center">{{ $item->sort_order }}</span>
                                    <button wire:click="moveDown({{ $item->id }})" class="p-1 hover:text-primary-600 transition-colors" title="Move down">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="table-cell">
                                @if($item->icon)
                                    <span class="text-lg">{{ $item->icon }}</span>
                                @else
                                    <span class="text-surface-300 dark:text-surface-600">—</span>
                                @endif
                            </td>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $item->title }}</td>
                            <td class="table-cell max-w-xs truncate">{{ $item->description }}</td>
                            <td class="table-cell">
                                @if($item->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('settings.why-choose-us.edit', $item->id) }}" wire:navigate class="btn-sm btn-outline">Edit</a>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this item?" class="btn-sm btn-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell text-center text-surface-400 py-12">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
