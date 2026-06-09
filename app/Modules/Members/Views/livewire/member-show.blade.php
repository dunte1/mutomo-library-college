<div>
    {{-- Inline header - avoids x-header component root element issue in Livewire --}}
    <div class="page-header flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">{{ $member->full_name }}</h1>
            <p class="page-subtitle">{{ $member->member_id }} · {{ ucfirst($member->membership_type) }}</p>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            @can('view-library-cards')
                <a href="{{ route('members.card', $member->id) }}" wire:navigate class="btn-outline btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    Library Card
                </a>
            @endcan
            @can('edit-members')
                <a href="{{ route('members.edit', $member->id) }}" wire:navigate class="btn-outline btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            @endcan
            @can('suspend-members')
                @if($member->status === 'active')
                    <button wire:click="suspend" wire:confirm="Are you sure you want to suspend this member?" class="btn-sm btn-danger">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Suspend
                    </button>
                @else
                    <button wire:click="activate" class="btn-sm btn-success">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Activate
                    </button>
                @endif
            @endcan
            @can('clear-members')
                @if($member->status !== 'cleared')
                    <button wire:click="clear" wire:confirm="Clear this member? They must have no active borrows and no outstanding fines." class="btn-sm btn-outline">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Clear
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <div class="md:hidden mb-4">
        <div class="card">
            <div class="card-body text-center py-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center overflow-hidden">
                    @if($member->photo)
                        <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->full_name }}" loading="lazy" class="w-full h-full object-cover">
                    @else
                        <span class="text-2xl font-bold text-primary-500 dark:text-primary-400">
                            {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                        </span>
                    @endif
                </div>
                <h4 class="text-lg font-semibold text-surface-900 dark:text-white mt-3">{{ $member->full_name }}</h4>
                <p class="text-xs text-surface-500 dark:text-surface-400 font-mono">{{ $member->member_id }}</p>
                <div class="mt-2">
                    @switch($member->status)
                        @case('active') <span class="badge-success">Active</span> @break
                        @case('suspended') <span class="badge-danger">Suspended</span> @break
                        @case('expired') <span class="badge-warning">Expired</span> @break
                        @case('inactive') <span class="badge-neutral">Inactive</span> @break
                        @case('cleared') <span class="badge-success">Cleared</span> @break
                    @endswitch
                </div>
                {{-- Mobile quick stats row --}}
                <div class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-surface-100 dark:border-surface-700">
                    <div>
                        <p class="text-xl font-bold text-surface-900 dark:text-white">{{ $totalBorrows }}</p>
                        <p class="text-[10px] text-surface-500 uppercase tracking-wide">Borrows</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalFines, 0) }}</p>
                        <p class="text-[10px] text-surface-500 uppercase tracking-wide">Total Fines</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($outstandingFines, 0) }}</p>
                        <p class="text-[10px] text-surface-500 uppercase tracking-wide">Outstanding</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Member Details card --}}
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Member Details</h3>
                        {{-- Status badge only on desktop (mobile shows it in hero) --}}
                        <div class="hidden md:block">
                            @switch($member->status)
                                @case('active') <span class="badge-success">Active</span> @break
                                @case('suspended') <span class="badge-danger">Suspended</span> @break
                                @case('expired') <span class="badge-warning">Expired</span> @break
                                @case('inactive') <span class="badge-neutral">Inactive</span> @break
                            @endswitch
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Email</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1 break-all">{{ $member->email ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Phone</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->phone ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Date of Birth</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->date_of_birth?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Gender</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1 capitalize">{{ $member->gender ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">National ID</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->id_number ?? 'N/A' }}</dd>
                        </div>
                        @if($member->membership_type === 'student')
                            <div>
                                <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Admission No.</dt>
                                <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->admission_number ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Year of Study</dt>
                                <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->class ?? 'N/A' }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Department</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->department?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Program</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->program?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Type</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1 capitalize">{{ $member->membership_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Joined</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->joined_at->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Expires</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->expires_at?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Registered By</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->registeredBy?->name ?? 'System' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Address</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $member->address ?? 'N/A' }}</dd>
                        </div>
                    </dl>

                    @if($member->notes)
                        <div class="mt-6 pt-6 border-t border-surface-100 dark:border-surface-700">
                            <h4 class="text-sm font-medium text-surface-900 dark:text-white mb-2">Notes</h4>
                            <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line">{{ $member->notes }}</p>
                        </div>
                    @endif

                    <div class="mt-6 pt-6 border-t border-surface-100 dark:border-surface-700">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mobile-form-actions">
                            <button wire:click="renew" class="btn-primary btn-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Renew Membership
                            </button>
                            @can('delete-members')
                                <button wire:click="delete" wire:confirm="Are you sure you want to delete this member? This action cannot be undone." class="btn-sm btn-danger">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- Borrowing History / Fines tabs --}}
            <div class="card">
                <div class="card-header">
                    {{-- Scrollable tab bar on mobile --}}
                    <div class="flex gap-1 overflow-x-auto scrollbar-thin -mx-1 px-1">
                        <button wire:click="setTab('details')" class="flex-shrink-0 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $tab === 'details' ? 'bg-surface-50 dark:bg-surface-700 text-surface-700 dark:text-surface-300' : 'text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700' }}">
                            Borrowing History
                        </button>
                        <button wire:click="setTab('fines')" class="flex-shrink-0 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $tab === 'fines' ? 'bg-surface-50 dark:bg-surface-700 text-surface-700 dark:text-surface-300' : 'text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700' }}">
                            Fines
                        </button>
                    </div>
                </div>

                @if($tab === 'details' || $tab === 'fines')
                    <div class="overflow-x-auto table-mobile-cards">
                        @if($tab === 'details')
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th class="table-header">Book</th>
                                        <th class="table-header">Borrowed</th>
                                        <th class="table-header">Due</th>
                                        <th class="table-header">Returned</th>
                                        <th class="table-header">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($borrowingHistory as $record)
                                        <tr>
                                            <td class="table-cell">
                                                <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $record->bookCopy?->book?->title ?? 'Unknown' }}</p>
                                                <p class="text-xs text-surface-500 font-mono">#{{ $record->bookCopy?->barcode ?? 'N/A' }}</p>
                                            </td>
                                            <td class="table-cell text-sm">{{ $record->borrowed_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="table-cell text-sm">{{ $record->due_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="table-cell text-sm">{{ $record->returned_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="table-cell">
                                                @switch($record->status)
                                                    @case('active') <span class="badge-info">Active</span> @break
                                                    @case('overdue') <span class="badge-danger">Overdue</span> @break
                                                    @case('returned') <span class="badge-success">Returned</span> @break
                                                    @case('lost') <span class="badge-danger">Lost</span> @break
                                                    @case('damaged') <span class="badge-warning">Damaged</span> @break
                                                    @default <span class="badge-neutral">{{ $record->status }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="table-cell text-center text-surface-400 py-8">No borrowing history.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @else
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th class="table-header">Reason</th>
                                        <th class="table-header">Amount</th>
                                        <th class="table-header">Paid</th>
                                        <th class="table-header">Waived</th>
                                        <th class="table-header">Status</th>
                                        <th class="table-header">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fines as $fine)
                                        <tr>
                                            <td class="table-cell text-sm">{{ $fine->reason ?? 'N/A' }}</td>
                                            <td class="table-cell text-sm font-medium">{{ number_format($fine->amount, 2) }}</td>
                                            <td class="table-cell text-sm">{{ number_format($fine->paid_amount ?? 0, 2) }}</td>
                                            <td class="table-cell text-sm">{{ number_format($fine->waived_amount ?? 0, 2) }}</td>
                                            <td class="table-cell">
                                                @switch($fine->status)
                                                    @case('pending') <span class="badge-warning">Pending</span> @break
                                                    @case('paid') <span class="badge-success">Paid</span> @break
                                                    @case('waived') <span class="badge-info">Waived</span> @break
                                                    @case('disputed') <span class="badge-danger">Disputed</span> @break
                                                    @default <span class="badge-neutral">{{ $fine->status }}</span>
                                                @endswitch
                                            </td>
                                            <td class="table-cell text-sm">{{ $fine->assessed_at?->format('d M Y') ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="table-cell text-center text-surface-400 py-8">No fines recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar — hidden on mobile (profile hero replaces it) --}}
        <div class="space-y-6 hidden md:block">
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Profile</h3>
                </div>
                <div class="card-body text-center">
                    <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center overflow-hidden">
                        @if($member->photo)
                            <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->full_name }}" loading="lazy" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl font-bold text-primary-500 dark:text-primary-400">
                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <h4 class="text-lg font-semibold text-surface-900 dark:text-white mt-4">{{ $member->full_name }}</h4>
                    <p class="text-sm text-surface-500 dark:text-surface-400">{{ $member->member_id }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Summary</h3>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Total Borrows</span>
                        <span class="text-sm font-semibold text-surface-900 dark:text-white">{{ $totalBorrows }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Total Fines</span>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ number_format($totalFines, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Outstanding Fines</span>
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">{{ number_format($outstandingFines, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($member->expires_at)
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Membership</h3>
                    </div>
                    <div class="card-body">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-surface-500 dark:text-surface-400">Expires</span>
                            <span class="text-sm font-semibold {{ $member->expires_at->isPast() ? 'text-red-600 dark:text-red-400' : 'text-surface-900 dark:text-white' }}">
                                {{ $member->expires_at->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-surface-500 dark:text-surface-400">Days Remaining</span>
                            <span class="text-sm font-semibold {{ $member->expires_at->isPast() ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $member->expires_at->isPast() ? 'Expired' : now()->diffInDays($member->expires_at) . ' days' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
