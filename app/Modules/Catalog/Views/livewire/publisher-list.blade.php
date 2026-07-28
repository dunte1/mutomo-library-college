@section('title', 'Publishers')
<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Publishers</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Manage book publishers</p>
            </div>
            <a href="{{ route('catalog.publishers.create') }}" wire:navigate class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Publisher
            </a>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search publishers..."
                    class="input-field pl-9">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header">Name</th>
                        <th class="table-header">Email</th>
                        <th class="table-header">Phone</th>
                        <th class="table-header">Books</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($publishers as $publisher)
                        <tr>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $publisher->name }}</td>
                            <td class="table-cell">{{ $publisher->email ?? '—' }}</td>
                            <td class="table-cell">{{ $publisher->phone ?? '—' }}</td>
                            <td class="table-cell">{{ $publisher->books_count }}</td>
                            <td class="table-cell">
                                @if($publisher->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('catalog.publishers.edit', $publisher->id) }}" wire:navigate class="btn-sm btn-outline">Edit</a>
                                    @can('delete-publishers')
                                        <button wire:click="delete({{ $publisher->id }})" wire:confirm="Delete this publisher? This action cannot be undone." class="btn-sm btn-danger">Delete</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell text-center text-surface-400 py-12">No publishers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($publishers->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $publishers->links() }}
            </div>
        @endif
    </div>
</div>
