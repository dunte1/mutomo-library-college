<div>
    <x-header title="Waitlist" subtitle="Members waiting for books" />

    <x-filter-bar>
        <x-input icon="search" placeholder="Search member or book..." wire:model.live.debounce="search" />
    </x-filter-bar>

    <x-card>
        <x-table>
            <x-thead>
                <x-tr>
                    <x-th>Member</x-th>
                    <x-th>Book</x-th>
                    <x-th>Date Requested</x-th>
                    <x-th>Status</x-th>
                    <x-th></x-th>
                </x-tr>
            </x-thead>
            <x-tbody>
                @forelse($entries as $entry)
                    <x-tr wire:key="{{ $entry->id }}">
                        <x-td>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $entry->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $entry->user->email }}</div>
                        </x-td>
                        <x-td>
                            <a href="{{ route('catalog.books.show', $entry->book_id) }}" wire:navigate
                                class="text-primary-600 hover:text-primary-800 dark:text-primary-400 font-medium">
                                {{ $entry->book->title }}
                            </a>
                        </x-td>
                        <x-td>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $entry->reserved_at->format('d/m/Y H:i') }}
                            </span>
                        </x-td>
                        <x-td>
                            <x-badge variant="warning">
                                Waiting
                            </x-badge>
                        </x-td>
                        <x-td class="text-right">
                            <x-btn sm variant="danger"
                                wire:click="delete({{ $entry->id }})"
                                wire:confirm="Remove this waitlist entry?">Remove</x-btn>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td colspan="5">
                            <x-empty-state icon="bookmark" title="No waitlist entries"
                                description="No members are currently waiting for books." />
                        </x-td>
                    </x-tr>
                @endforelse
            </x-tbody>
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>
</div>
