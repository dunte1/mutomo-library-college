@section('title', 'Programs')
<div>
    <x-header title="Programs" subtitle="Manage academic programs and classes">
        <x-slot:actions>
            <a href="{{ route('settings.programs.create') }}" wire:navigate class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Program
            </a>
        </x-slot:actions>
    </x-header>

    <div class="card">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header">Name</th>
                        <th class="table-header">Code</th>
                        <th class="table-header">Department</th>
                        <th class="table-header">Duration</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                        <tr>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $program->name }}</td>
                            <td class="table-cell font-mono text-sm">{{ $program->code }}</td>
                            <td class="table-cell">{{ $program->department?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $program->duration_years }} year(s)</td>
                            <td class="table-cell">
                                @if($program->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('settings.programs.edit', $program->id) }}" wire:navigate class="btn-sm btn-outline">Edit</a>
                                    <button wire:click="delete({{ $program->id }})" wire:confirm="Delete this program?" class="btn-sm btn-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell text-center text-surface-400 py-12">No programs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($programs->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
</div>
