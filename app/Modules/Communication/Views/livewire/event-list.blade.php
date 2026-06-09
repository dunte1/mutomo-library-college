@section('title', 'Events')
<div>
    <x-header title="Events" subtitle="Manage library and institutional events">
        <x-slot:actions>
            <a href="{{ route('communication.events.create') }}" wire:navigate class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Event
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
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search events..."
                        class="input-field pl-9">
                </div>
                <select wire:model.live="statusFilter" class="input-field w-full sm:w-48">
                    <option value="">All Status</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
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
                        <th class="table-header">Location</th>
                        <th class="table-header">Start Date</th>
                        <th class="table-header">End Date</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $event->title }}</td>
                            <td class="table-cell">
                                <span class="badge-info">{{ ucfirst($event->type) }}</span>
                            </td>
                            <td class="table-cell">{{ $event->location }}</td>
                            <td class="table-cell">{{ $event->start_date?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="table-cell">{{ $event->end_date?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="table-cell">
                                @switch($event->status)
                                    @case('upcoming')
                                        <span class="badge-info">Upcoming</span>
                                        @break
                                    @case('ongoing')
                                        <span class="badge-success">Ongoing</span>
                                        @break
                                    @case('completed')
                                        <span class="badge-neutral">Completed</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge-error">Cancelled</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('communication.events.edit', $event->id) }}" wire:navigate class="btn-sm btn-outline">Edit</a>
                                    <button wire:click="delete({{ $event->id }})" wire:confirm="Delete this event?" class="btn-sm btn-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-cell text-center text-surface-400 py-12">No events found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($events->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
