@section('title', 'Access Levels')
<div>
    <x-slot name="header">Access Levels</x-slot>
    <x-slot name="subtitle">Manage digital asset access levels</x-slot>

    <div class="card mb-6">
        <div class="card-body">
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search access levels..."
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
                        <th class="table-header">Code</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accessLevels as $accessLevel)
                        <tr>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $accessLevel->name }}</td>
                            <td class="table-cell font-mono text-sm">{{ $accessLevel->code }}</td>
                            <td class="table-cell text-surface-500">{{ $accessLevel->description ?? '—' }}</td>
                            <td class="table-cell">
                                @if($accessLevel->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <button wire:click="toggleStatus({{ $accessLevel->id }})" class="btn-sm {{ $accessLevel->is_active ? 'btn-warning' : 'btn-outline' }}">
                                        {{ $accessLevel->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button wire:click="delete({{ $accessLevel->id }})" wire:confirm="Delete this access level?" class="btn-sm btn-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-cell text-center text-surface-400 py-12">No access levels found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accessLevels->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $accessLevels->links() }}
            </div>
        @endif
    </div>
</div>
