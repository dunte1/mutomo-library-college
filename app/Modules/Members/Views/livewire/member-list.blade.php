@section('title', 'Members')
<div>
    <x-slot name="header">Members</x-slot>
    <x-slot name="subtitle">Manage students, staff, and faculty</x-slot>

    <div class="flex justify-end gap-2 mb-6">
        @can('create-members')
            <a href="{{ route('members.create') }}" wire:navigate class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Register Member
            </a>
        @endcan
        <button wire:click="exportCsv" class="btn-outline">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Total</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Active</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Suspended</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['suspended'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Expired</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['expired'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">New This Month</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $stats['newThisMonth'] }}</p>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="Search by name, email, phone, or ID..."
                            class="input-field pl-9">
                    </div>
                </div>
                <div>
                    <select wire:model.live="status" id="status" class="input-field">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="expired">Expired</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="membershipType" id="membershipType" class="input-field">
                        <option value="">All Types</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="staff">Staff</option>
                        <option value="external">External</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end mt-3">
                <button wire:click="clearFilters" class="btn-ghost text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header">Member ID</th>
                        <th class="table-header">Name</th>
                        <th class="table-header">Email / Phone</th>
                        <th class="table-header">Department</th>
                        <th class="table-header">Program</th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Joined</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td class="table-cell font-mono text-xs">{{ $member->member_id }}</td>
                            <td class="table-cell">
                                <a href="{{ route('members.show', $member->id) }}" wire:navigate class="font-medium text-surface-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    {{ $member->full_name }}
                                </a>
                            </td>
                            <td class="table-cell">
                                <p class="text-sm text-surface-900 dark:text-white">{{ $member->email ?? '—' }}</p>
                                <p class="text-xs text-surface-500">{{ $member->phone ?? '—' }}</p>
                            </td>
                            <td class="table-cell text-sm">{{ $member->department?->name ?? '—' }}</td>
                            <td class="table-cell text-sm">{{ $member->program?->name ?? '—' }}</td>
                            <td class="table-cell">
                                <span class="capitalize">{{ $member->membership_type }}</span>
                            </td>
                            <td class="table-cell">
                                @switch($member->status)
                                    @case('active')
                                        <span class="badge-success">Active</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge-danger">Suspended</span>
                                        @break
                                    @case('expired')
                                        <span class="badge-warning">Expired</span>
                                        @break
                                    @case('inactive')
                                        <span class="badge-neutral">Inactive</span>
                                        @break
                                    default
                                        <span class="badge-neutral">{{ $member->status }}</span>
                                @endswitch
                            </td>
                            <td class="table-cell text-sm">{{ $member->joined_at->format('d M Y') }}</td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('members.show', $member->id) }}" wire:navigate class="btn-sm btn-outline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </a>
                                    @can('edit-members')
                                        <a href="{{ route('members.edit', $member->id) }}" wire:navigate class="btn-sm btn-outline">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="table-cell text-center text-surface-400 py-12">
                                <svg class="w-12 h-12 mx-auto text-surface-300 dark:text-surface-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm font-medium text-surface-500 dark:text-surface-400">No members found</p>
                                <p class="text-xs text-surface-400 dark:text-surface-500 mt-1">Try adjusting your search or filter criteria.</p>
                                @if($search || $status || $membershipType)
                                    <button wire:click="clearFilters" class="btn-primary btn-sm mt-3">Clear Filters</button>
                                @else
                                    <a href="{{ route('members.create') }}" wire:navigate class="btn-primary btn-sm mt-3">Register First Member</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($members->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
