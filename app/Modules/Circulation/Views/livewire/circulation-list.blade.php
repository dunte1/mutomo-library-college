@section('title', 'Circulation')
<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Circulation</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Manage active borrows, returns, and overdues</p>
            </div>
            <div class="flex items-center gap-2">
                @can('borrow-books')
                    <a href="{{ route('circulation.issue') }}" wire:navigate class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Issue Book
                    </a>
                @endcan
                <button wire:click="exportCsv" class="btn-outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Active</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white mt-1">{{ $stats['active_borrows'] ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Overdue</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['overdue_borrows'] ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Borrowed Today</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white mt-1">{{ $stats['borrowed_today'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Returned Today</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white mt-1">{{ $stats['returned_today'] }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="flex flex-wrap gap-1">
                <button wire:click="setTab('active')" class="px-3 sm:px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $tab === 'active' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700' }}">
                    Active ({{ $stats['active_borrows'] }})
                </button>
                <button wire:click="setTab('overdue')" class="px-3 sm:px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $tab === 'overdue' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' : 'text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700' }}">
                    Overdue ({{ $stats['overdue_borrows'] }})
                </button>
                <button wire:click="setTab('history')" class="px-3 sm:px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $tab === 'history' ? 'bg-surface-50 dark:bg-surface-700 text-surface-700 dark:text-surface-300' : 'text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700' }}">
                    History
                </button>
            </div>
        </div>
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header">Member</th>
                        <th class="table-header">Book</th>
                        <th class="table-header">Borrowed</th>
                        <th class="table-header">Due</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="table-cell">
                                <p class="font-medium text-surface-900 dark:text-white">{{ $record->user->name }}</p>
                                <p class="text-xs text-surface-500">{{ $record->user->email }}</p>
                            </td>
                            <td class="table-cell">
                                <p class="text-sm text-surface-900 dark:text-white">{{ $record->bookCopy->book->title }}</p>
                                <p class="text-xs text-surface-500 font-mono">#{{ $record->bookCopy->barcode }}</p>
                            </td>
                            <td class="table-cell">{{ $record->borrowed_at->format('d M Y') }}</td>
                            <td class="table-cell">
                                <span class="{{ $record->isOverdue() ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                    {{ $record->due_at->format('d M Y') }}
                                </span>
                            </td>
                            <td class="table-cell">
                                @switch($record->status)
                                    @case('active')
                                        <span class="badge-info">Active</span>
                                        @break
                                    @case('overdue')
                                        <span class="badge-danger">Overdue</span>
                                        @break
                                    @case('returned')
                                        <span class="badge-success">Returned</span>
                                        @break
                                    @case('lost')
                                        <span class="badge-danger">Lost</span>
                                        @break
                                    @case('damaged')
                                        <span class="badge-warning">Damaged</span>
                                        @break
                                    default
                                        <span class="badge-neutral">{{ $record->status }}</span>
                                @endswitch
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    @if($record->isActive())
                                        <a href="{{ route('circulation.return') }}?barcode={{ $record->bookCopy->barcode }}" wire:navigate class="btn-sm btn-outline">Return</a>
                                        <button wire:click="markAsLost({{ $record->id }})"
                                            wire:confirm="Mark this book as LOST? A fine of KES 1,500 will be assessed."
                                            class="btn-sm btn-outline text-red-600 border-red-300 hover:bg-red-50">
                                            Lost
                                        </button>
                                        <button wire:click="markAsDamaged({{ $record->id }})"
                                            wire:confirm="Mark this book as DAMAGED? A fine of KES 500 will be assessed."
                                            class="btn-sm btn-outline text-amber-600 border-amber-300 hover:bg-amber-50">
                                            Damaged
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell text-center text-surface-400 py-8">
                                @if($tab === 'active')
                                    No active borrows found.
                                @elseif($tab === 'overdue')
                                    No overdue items. Great job!
                                @else
                                    No borrow history yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
