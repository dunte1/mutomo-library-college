@section('title', 'Audit Logs')
<div>
    <x-slot name="header">Audit Logs</x-slot>
    <x-slot name="subtitle">View system activity, user actions and security events</x-slot>

    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="label">Search User</label>
                    <input type="text" wire:model.live.debounce.300ms="searchUser" placeholder="Name or email..."
                           class="input-field w-full">
                </div>
                <div>
                    <label class="label">Event Type</label>
                    <select wire:model.live="event" class="input-field w-full">
                        <option value="">All Events</option>
                        @foreach($events as $e)
                            <option value="{{ $e }}">{{ str_replace('-', ' ', ucwords($e)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">From Date</label>
                    <input type="date" wire:model.live="dateFrom" class="input-field w-full">
                </div>
                <div>
                    <label class="label">To Date</label>
                    <input type="date" wire:model.live="dateTo" class="input-field w-full">
                </div>
            </div>
        </div>
    </div>

    @can('clear-audit-logs')
        <div class="mb-4 flex justify-end">
            <button wire:click="clearOldLogs" wire:confirm="Are you sure you want to clear logs older than 90 days?"
                    class="btn-outline text-sm text-accent-600 border-accent-200 hover:bg-accent-50 dark:hover:bg-accent-900/20">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Clear Old Logs
            </button>
        </div>
    @endcan

    <div class="card">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header">User</th>
                        <th class="table-header">Action</th>
                        <th class="table-header">Subject</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">IP Address</th>
                        <th class="table-header">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-surface-100 dark:border-surface-700/50">
                            <td class="table-cell">
                                <p class="font-medium text-surface-900 dark:text-white">{{ $log->causer?->name ?? 'System' }}</p>
                                <p class="text-xs text-surface-500">{{ $log->causer?->email ?? '' }}</p>
                            </td>
                            <td class="table-cell">
                                <span class="badge-neutral text-xs">
                                    {{ str_replace('-', ' ', ucwords($log->event ?? 'unknown')) }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <p class="text-sm text-surface-900 dark:text-white">
                                    {{ class_basename($log->subject_type) }}#{{ $log->subject_id }}
                                </p>
                            </td>
                            <td class="table-cell text-sm text-surface-600 dark:text-surface-400 max-w-xs truncate">
                                {{ $log->description }}
                            </td>
                            <td class="table-cell">
                                <code class="text-xs bg-surface-100 dark:bg-surface-700 px-2 py-1 rounded">
                                    {{ $log->getExtraProperty('ip') ?? 'N/A' }}
                                </code>
                            </td>
                            <td class="table-cell text-sm text-surface-500">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell text-center text-surface-400 py-8">
                                <svg class="w-12 h-12 mx-auto mb-3 text-surface-300 dark:text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p>No audit logs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
