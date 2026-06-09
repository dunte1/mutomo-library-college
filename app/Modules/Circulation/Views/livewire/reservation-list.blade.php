<div>
    <x-header title="Reservations" subtitle="Manage book holds and reservations" />

    <x-stats-bar :stats="[
        ['label' => 'Pending', 'value' => $stats['pending'], 'color' => 'warning'],
        ['label' => 'Fulfilled', 'value' => $stats['fulfilled'], 'color' => 'success'],
        ['label' => 'Cancelled', 'value' => $stats['cancelled'], 'color' => 'danger'],
        ['label' => 'Expired', 'value' => $stats['expired'], 'color' => 'default'],
    ]" />

    <x-filter-bar>
        <x-input icon="search" placeholder="Search book or patron..." wire:model.live.debounce="search" />
        <x-select wire:model.live="status">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="fulfilled">Fulfilled</option>
            <option value="cancelled">Cancelled</option>
            <option value="expired">Expired</option>
        </x-select>
        @if($search || $status)
            <x-btn text-xs wire:click="clearFilters">Clear</x-btn>
        @endif
    </x-filter-bar>

    <x-card>
        <x-table>
            <x-thead>
                <x-tr>
                    <x-th>Patron</x-th>
                    <x-th>Book</x-th>
                    <x-th>Reserved</x-th>
                    <x-th>Expires</x-th>
                    <x-th>Status</x-th>
                    <x-th></x-th>
                </x-tr>
            </x-thead>
            <x-tbody>
                @forelse($reservations as $reservation)
                    <x-tr wire:key="{{ $reservation->id }}">
                        <x-td>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $reservation->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $reservation->user->email }}</div>
                        </x-td>
                        <x-td>
                            <a href="{{ route('catalog.books.show', $reservation->book_id) }}" wire:navigate
                                class="text-primary-600 hover:text-primary-800 dark:text-primary-400 font-medium">
                                {{ $reservation->book->title }}
                            </a>
                        </x-td>
                        <x-td>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $reservation->reserved_at->format('d/m/Y H:i') }}
                            </span>
                        </x-td>
                        <x-td>
                            <span class="text-sm {{ $reservation->expires_at->isPast() && $reservation->status === 'pending' ? 'text-red-600 font-medium' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $reservation->expires_at->format('d/m/Y H:i') }}
                            </span>
                        </x-td>
                        <x-td>
                            <x-badge :variant="match($reservation->status) {
                                'pending' => 'warning',
                                'fulfilled' => 'success',
                                'cancelled' => 'danger',
                                'expired' => 'default',
                                default => 'default',
                            }">
                                {{ ucfirst($reservation->status) }}
                            </x-badge>
                        </x-td>
                        <x-td class="text-right">
                            @if($reservation->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <x-btn sm variant="danger"
                                        wire:click="cancelAsStaff({{ $reservation->id }})"
                                        wire:confirm="Cancel this reservation?">Cancel</x-btn>
                                </div>
                            @endif
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td colspan="6">
                            <x-empty-state icon="bookmark" title="No reservations found"
                                description="Reservations will appear here when patrons place holds." />
                        </x-td>
                    </x-tr>
                @endforelse
            </x-tbody>
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $reservations->links() }}
    </div>
</div>
