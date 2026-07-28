@section('title', 'Library Cards')
<div>
    <x-slot name="header">Library Cards</x-slot>
    <x-slot name="subtitle">Manage and view all library cards</x-slot>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Total Cards</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Active</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Lost</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['lost'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Replaced</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['replaced'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Issued This Month</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $stats['issued_this_month'] }}</p>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by card number, member name or ID..."
                            class="input-field pl-9">
                    </div>
                </div>
                <div>
                    <select wire:model.live="status" class="input-field">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="lost">Lost</option>
                        <option value="replaced">Replaced</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Card Number</th>
                            <th>Member</th>
                            <th>Status</th>
                            <th>Issued</th>
                            <th>Expires</th>
                            <th>Issued By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                        <tr>
                            <td>
                                <span class="font-mono text-sm">{{ $card->card_number }}</span>
                            </td>
                            <td>
                                <a href="{{ route('members.show', $card->member_id) }}" wire:navigate class="text-primary-600 hover:underline">
                                    {{ $card->member->full_name ?? 'N/A' }}
                                </a>
                                <p class="text-xs text-surface-500">{{ $card->member->member_id ?? '' }}</p>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'lost' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        'replaced' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                        'expired' => 'bg-surface-100 text-surface-800 dark:bg-surface-700 dark:text-surface-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$card->status] ?? '' }}">
                                    {{ ucfirst($card->status) }}
                                </span>
                            </td>
                            <td class="text-sm text-surface-600 dark:text-surface-400">{{ $card->issued_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="text-sm text-surface-600 dark:text-surface-400">{{ $card->expires_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="text-sm text-surface-600 dark:text-surface-400">{{ $card->issuer->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('members.card', $card->member_id) }}" wire:navigate class="btn-sm btn-outline">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-surface-500">
                                No library cards found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $cards->links() }}
            </div>
        </div>
    </div>
</div>
