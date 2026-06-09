@section('title', 'Announcements')
<div>
    <x-header title="Announcements" subtitle="Manage library announcements and notices">
        <x-slot:actions>
            <a href="{{ route('communication.announcements.create') }}" wire:navigate class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Announcement
            </a>
        </x-slot:actions>
    </x-header>

    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search announcements..."
                        class="input-field pl-9">
                </div>
                <select wire:model.live="statusFilter" class="input-field w-full sm:w-48">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header">Title</th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Published</th>
                        <th class="table-header">Expires</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $announcement->title }}</td>
                            <td class="table-cell">
                                @switch($announcement->type)
                                    @case('important')
                                        <span class="badge-error">Important</span>
                                        @break
                                    @case('warning')
                                        <span class="badge-warning">Warning</span>
                                        @break
                                    @default
                                        <span class="badge-info">Info</span>
                                @endswitch
                            </td>
                            <td class="table-cell">
                                @if($announcement->status === 'published')
                                    <span class="badge-success">Published</span>
                                @else
                                    <span class="badge-neutral">Draft</span>
                                @endif
                            </td>
                            <td class="table-cell">{{ $announcement->published_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="table-cell">{{ $announcement->expires_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('communication.announcements.edit', $announcement->id) }}" wire:navigate class="btn-sm btn-outline">Edit</a>
                                    <button wire:click="delete({{ $announcement->id }})" wire:confirm="Delete this announcement?" class="btn-sm btn-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell text-center text-surface-400 py-12">No announcements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($announcements->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
