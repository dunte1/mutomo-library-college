@section('title', 'Departments')
<div>
    <x-header title="Departments" subtitle="Manage institutional departments">
        <x-slot:actions>
            <a href="{{ route('settings.departments.create') }}" wire:navigate class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            Add Department
            </a>
        </x-slot:actions>
    </x-header>

    <div class="card mb-6">
        <div class="card-body">
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search departments..."
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
                        <th class="table-header">Users</th>
                        <th class="table-header">Programs</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $department->name }}</td>
                            <td class="table-cell font-mono text-sm">{{ $department->code }}</td>
                            <td class="table-cell">{{ $department->users_count }}</td>
                            <td class="table-cell">{{ $department->programs_count }}</td>
                            <td class="table-cell">
                                @if($department->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('settings.departments.edit', $department->id) }}" wire:navigate class="btn-sm btn-outline">Edit</a>
                                    <button wire:click="delete({{ $department->id }})" wire:confirm="Delete this department?" class="btn-sm btn-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell text-center text-surface-400 py-12">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departments->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
</div>
