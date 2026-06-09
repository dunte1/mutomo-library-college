<div>
    <x-header title="Override Due Dates" subtitle="Modify due dates for active borrows" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="Active Borrows">
                <div class="mb-4">
                    <x-input icon="search" placeholder="Search by patron name, email, or book title..."
                        wire:model.live.debounce="search" />
                </div>

                <x-table>
                    <x-thead>
                        <x-tr>
                            <x-th>Patron</x-th>
                            <x-th>Book</x-th>
                            <x-th>Due Date</x-th>
                            <x-th></x-th>
                        </x-tr>
                    </x-thead>
                    <x-tbody>
                        @forelse($borrows as $borrow)
                            <x-tr wire:key="{{ $borrow->id }}" class="{{ $borrow->id === $selectedBorrowId ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                                <x-td>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $borrow->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $borrow->user->email }}</div>
                                </x-td>
                                <x-td>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $borrow->bookCopy->book->title }}
                                    </span>
                                </x-td>
                                <x-td>
                                    <span class="text-sm {{ $borrow->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-600 dark:text-gray-400' }}">
                                        {{ $borrow->due_at->format('d/m/Y') }}
                                    </span>
                                </x-td>
                                <x-td class="text-right">
                                    <x-btn sm variant="{{ $borrow->id === $selectedBorrowId ? 'danger' : 'primary' }}"
                                        wire:click="selectBorrow({{ $borrow->id }})">
                                        {{ $borrow->id === $selectedBorrowId ? 'Selected' : 'Select' }}
                                    </x-btn>
                                </x-td>
                            </x-tr>
                        @empty
                            <x-tr>
                                <x-td colspan="4">
                                    <x-empty-state icon="book-open" title="No active borrows found"
                                        description="Active borrows will appear here." />
                                </x-td>
                            </x-tr>
                        @endforelse
                    </x-tbody>
                </x-table>

                <div class="mt-4">
                    {{ $borrows->links() }}
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-1">
            <x-card title="Override Form">
                @if($selectedBorrowId)
                    <form wire:submit="override" class="space-y-4">
                        <div>
                            <x-input-label for="newDueDate" value="New Due Date" />
                            <x-input id="newDueDate" type="date" wire:model="newDueDate" />
                            @error('newDueDate') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-input-label for="reason" value="Reason for Override" />
                            <textarea id="reason" wire:model="reason" rows="3"
                                placeholder="Explain why the due date is being changed..."
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                            @error('reason') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-2">
                            <x-btn primary type="submit">Override Due Date</x-btn>
                            <x-btn type="button" variant="secondary"
                                wire:click="$set('selectedBorrowId', null)">Cancel</x-btn>
                        </div>
                    </form>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Select an active borrow from the list to override its due date.
                    </p>
                @endif
            </x-card>
        </div>
    </div>
</div>
