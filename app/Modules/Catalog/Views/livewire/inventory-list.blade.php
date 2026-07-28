<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Inventory</h1>
            <p class="page-subtitle">Manage book copies and stock levels</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="stat-card">
            <p class="stat-label">Total Copies</p>
            <p class="stat-value">{{ $stats['total'] }}</p>
        </div>
        <div class="stat-card bg-emerald-50 dark:bg-emerald-900/20">
            <p class="stat-label text-emerald-600 dark:text-emerald-400">Available</p>
            <p class="stat-value text-emerald-600 dark:text-emerald-400">{{ $stats['available'] }}</p>
        </div>
        <div class="stat-card bg-blue-50 dark:bg-blue-900/20">
            <p class="stat-label text-blue-600 dark:text-blue-400">Borrowed</p>
            <p class="stat-value text-blue-600 dark:text-blue-400">{{ $stats['borrowed'] }}</p>
        </div>
        <div class="stat-card bg-amber-50 dark:bg-amber-900/20">
            <p class="stat-label text-amber-600 dark:text-amber-400">Damaged</p>
            <p class="stat-value text-amber-600 dark:text-amber-400">{{ $stats['damaged'] }}</p>
        </div>
        <div class="stat-card bg-red-50 dark:bg-red-900/20">
            <p class="stat-label text-red-600 dark:text-red-400">Lost</p>
            <p class="stat-value text-red-600 dark:text-red-400">{{ $stats['lost'] }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce="search" placeholder="Search by title, barcode, or location..." class="input w-full">
                </div>
                <select wire:model.live="status" class="input w-full md:w-48">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="borrowed">Borrowed</option>
                    <option value="damaged">Damaged</option>
                    <option value="lost">Lost</option>
                    <option value="withdrawn">Withdrawn</option>
                </select>
                @if($search || $status)
                <button wire:click="clearFilters" class="btn-sm btn-secondary">Clear</button>
                @endif
            </div>

            <div class="overflow-x-auto table-mobile-cards">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Added</th>
                            @can('manage-inventory')
                            <th>Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($copies as $copy)
                        <tr>
                            <td class="font-mono text-sm">{{ $copy->barcode ?? '—' }}</td>
                            <td>
                                <a href="{{ route('catalog.books.show', $copy->book_id) }}" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ $copy->book->title ?? 'Unknown' }}
                                </a>
                            </td>
                            <td>
                                @php
                                    $statusColors = ['available' => 'badge-success', 'borrowed' => 'badge-info', 'damaged' => 'badge-warning', 'lost' => 'badge-danger', 'withdrawn' => 'badge-secondary'];
                                @endphp
                                <span class="badge {{ $statusColors[$copy->status] ?? 'badge-secondary' }}">{{ ucfirst($copy->status) }}</span>
                            </td>
                            <td class="text-sm text-surface-500">{{ $copy->shelf_location ?? '—' }}</td>
                            <td class="text-sm text-surface-500">{{ $copy->created_at->format('M d, Y') }}</td>
                            @can('manage-inventory')
                            <td>
                                <div class="flex items-center gap-1 flex-wrap">
                                    @if($copy->status === 'available')
                                        <button wire:click="markDamaged({{ $copy->id }})" class="text-xs text-amber-600 hover:text-amber-800">Damaged</button>
                                        <button wire:click="markLost({{ $copy->id }})" class="text-xs text-red-600 hover:text-red-800">Lost</button>
                                    @elseif($copy->status === 'damaged' || $copy->status === 'lost')
                                        <button wire:click="markAvailable({{ $copy->id }})" class="text-xs text-emerald-600 hover:text-emerald-800">Available</button>
                                    @endif
                                    @if($copy->status !== 'withdrawn' && $copy->status !== 'borrowed')
                                        <button wire:click="markWithdrawn({{ $copy->id }})" wire:confirm="Withdraw this copy permanently?" class="text-xs text-surface-400 hover:text-surface-600">Withdraw</button>
                                    @endif
                                </div>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->can('manage-inventory') ? 6 : 5 }}" class="text-center py-8 text-surface-400">No copies found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $copies->links() }}
            </div>
        </div>
    </div>
</div>
