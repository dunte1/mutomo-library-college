@section('title', 'Fine Management')
<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Fine Management</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Manage and process library fines</p>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row gap-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by member name..." class="input-field flex-1">
                <select wire:model.live="status" class="input-field">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="waived">Waived</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <x-th>Member</x-th>
                        <x-th>Book</x-th>
                        <x-th>Reason</x-th>
                        <x-th>Amount</x-th>
                        <x-th>Paid</x-th>
                        <x-th>Status</x-th>
                        <x-th>Actions</x-th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fines as $fine)
                        <x-tr>
                            <x-td>{{ $fine->user?->name ?? 'Unknown' }}</x-td>
                            <x-td>{{ $fine->borrowRecord?->bookCopy?->book?->title ?? 'N/A' }}</x-td>
                            <x-td class="max-w-xs truncate">{{ $fine->reason }}</x-td>
                            <x-td>KES {{ number_format($fine->amount, 2) }}</x-td>
                            <x-td>KES {{ number_format($fine->paid_amount, 2) }}</x-td>
                            <x-td>
                                <span class="badge badge-{{ $fine->status === 'pending' ? 'warning' : ($fine->status === 'paid' ? 'success' : 'info') }}">
                                    {{ ucfirst($fine->status) }}
                                </span>
                            </x-td>
                            <x-td>
                                @if ($fine->status === 'pending')
                                    <div class="flex items-center gap-2">
                                        <button wire:click="payFine({{ $fine->id }})" wire:confirm="Mark this fine as fully paid?" class="text-sm text-green-600 hover:text-green-800">Pay</button>
                                        <button wire:click="confirmWaive({{ $fine->id }})" class="text-sm text-amber-600 hover:text-amber-800">Waive</button>
                                    </div>
                                @else
                                    <span class="text-sm text-surface-400">—</span>
                                @endif
                            </x-td>
                        </x-tr>
                    @empty
                        <x-tr>
                            <x-td colspan="7">
                                <div class="text-center py-8 text-surface-500">No fines found.</div>
                            </x-td>
                        </x-tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-t">
            {{ $fines->links() }}
        </div>
    </div>

    @if ($fineIdToWaive)
        <x-modal>
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Waive Fine</h3>
                <x-field label="Reason for waiving">
                    <textarea wire:model="waiveReason" class="input-field" rows="3" placeholder="Enter reason..."></textarea>
                </x-field>
                @error('waiveReason') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('fineIdToWaive', 0)" class="btn-outline">Cancel</button>
                    <button wire:click="waiveFine()" class="btn-primary">Confirm Waive</button>
                </div>
            </div>
        </x-modal>
    @endif
</div>
