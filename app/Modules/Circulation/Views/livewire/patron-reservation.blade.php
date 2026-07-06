@section('title', 'My Reservations')
<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-surface-900 dark:text-white">My Reservations</h2>
        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Place holds on books and manage your reservations</p>
    </div>

    <div class="card mb-6">
        <div class="card-header">
            <h3 class="font-semibold text-surface-900 dark:text-white">Place a Hold</h3>
        </div>
        <div class="card-body">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by title or ISBN..." class="input-field w-full mb-4">
            @if ($books->count())
                <div class="space-y-2">
                    @foreach ($books as $book)
                        <div class="flex items-center justify-between p-3 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-800/50">
                            <div>
                                <p class="font-medium text-surface-900 dark:text-white">{{ $book->title }}</p>
                                <p class="text-sm text-surface-500">{{ $book->author ?? 'Unknown author' }} &middot; {{ $book->isbn ?? 'No ISBN' }}</p>
                            </div>
                            <button wire:click="placeHold({{ $book->id }})" class="btn-primary text-sm">Place Hold</button>
                        </div>
                    @endforeach
                </div>
            @elseif ($this->search)
                <p class="text-sm text-surface-500 text-center py-4">No books found matching your search.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-surface-900 dark:text-white">Current Reservations</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <x-th>Book</x-th>
                        <x-th>Reserved</x-th>
                        <x-th>Expires</x-th>
                        <x-th>Status</x-th>
                        <x-th>Actions</x-th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                        <x-tr>
                            <x-td>{{ $reservation->book?->title ?? 'N/A' }}</x-td>
                            <x-td>{{ $reservation->reserved_at?->format('M d, Y') ?? '—' }}</x-td>
                            <x-td>{{ $reservation->expires_at?->format('M d, Y') ?? '—' }}</x-td>
                            <x-td>
                                <span class="badge badge-{{ $reservation->status === 'pending' ? 'warning' : ($reservation->status === 'fulfilled' ? 'success' : 'info') }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </x-td>
                            <x-td>
                                @if ($reservation->status === 'pending')
                                    <button wire:click="cancelHold({{ $reservation->id }})" wire:confirm="Cancel this reservation?" class="text-sm text-red-600 hover:text-red-800">Cancel</button>
                                @else
                                    <span class="text-sm text-surface-400">—</span>
                                @endif
                            </x-td>
                        </x-tr>
                    @empty
                        <x-tr>
                            <x-td colspan="5">
                                <div class="text-center py-8 text-surface-500">No reservations yet.</div>
                            </x-td>
                        </x-tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
