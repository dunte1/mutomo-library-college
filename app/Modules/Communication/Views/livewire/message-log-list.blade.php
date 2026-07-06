<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Message Logs</h1>
            <p class="page-subtitle">History of all sent messages</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="stat-card">
            <p class="stat-label">Total Messages</p>
            <p class="stat-value">{{ $stats['total'] }}</p>
        </div>
        <div class="stat-card bg-blue-50 dark:bg-blue-900/20">
            <p class="stat-label text-blue-600 dark:text-blue-400">Broadcasts</p>
            <p class="stat-value text-blue-600 dark:text-blue-400">{{ $stats['broadcasts'] }}</p>
        </div>
        <div class="stat-card bg-emerald-50 dark:bg-emerald-900/20">
            <p class="stat-label text-emerald-600 dark:text-emerald-400">Direct Messages</p>
            <p class="stat-value text-emerald-600 dark:text-emerald-400">{{ $stats['direct'] }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce="search" placeholder="Search messages..." class="input w-full">
                </div>
                <select wire:model.live="type" class="input w-full md:w-48">
                    <option value="">All Types</option>
                    <option value="direct">Direct</option>
                    <option value="group">Group</option>
                    <option value="broadcast">Broadcast</option>
                    <option value="department">Department</option>
                    <option value="program">Program</option>
                </select>
                @if($search || $type)
                <button wire:click="clearFilters" class="btn-sm btn-secondary">Clear</button>
                @endif
            </div>

            <div class="overflow-x-auto table-mobile-cards">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Sender</th>
                                <th>Recipients</th>
                                <th>Replies</th>
                                <th>Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $message)
                            <tr>
                                <td class="font-medium max-w-xs truncate">
                                    @if($message->parent_id)
                                    <span class="text-xs text-surface-400 mr-1">↳</span>
                                    @endif
                                    {{ $message->subject ?? '(No subject)' }}
                                </td>
                                <td><span class="badge badge-{{ $message->type === 'broadcast' ? 'warning' : 'info' }}">{{ ucfirst($message->type) }}</span></td>
                                <td class="text-sm text-surface-500">{{ $message->sender?->name ?? 'System' }}</td>
                                <td class="text-sm text-surface-500">{{ $message->recipients->count() }}</td>
                                <td class="text-sm text-surface-500">{{ $message->replies_count }}</td>
                                <td class="text-sm text-surface-500">{{ $message->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-surface-400">No messages found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>

            <div class="mt-4">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>
