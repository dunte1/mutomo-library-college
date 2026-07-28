@section('title', 'Fines')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->can('manage-fines') ? 'Fine Management' : 'Fines Overview' }}</h1>

    <div class="card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search user or book..."
                       class="input-field w-full">
            </div>
            <div>
                <select wire:model.live="status" class="input-field w-full">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="waived">Waived</option>
                    <option value="disputed">Disputed</option>
                </select>
            </div>
            <div>
                <select wire:model.live="type" class="input-field w-full">
                    <option value="">All Types</option>
                    <option value="overdue">Overdue</option>
                    <option value="lost_book">Lost Book</option>
                    <option value="damage">Damage</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Book</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Amount (KES)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fines as $fine)
                        <tr class="border-t dark:border-gray-700">
                            <td class="py-3 px-4 text-gray-800 dark:text-gray-200">{{ $fine->borrowRecord->user->name }}</td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $fine->borrowRecord->bookCopy->book->title }}</td>
                            <td class="py-3 px-4"><span class="badge">{{ ucwords(str_replace('_', ' ', $fine->type)) }}</span></td>
                            <td class="py-3 px-4 text-right font-medium text-gray-800 dark:text-gray-200">{{ number_format($fine->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="badge {{ $fine->status === 'paid' ? 'badge-success' : ($fine->status === 'waived' ? 'badge-warning' : ($fine->status === 'disputed' ? 'badge-danger' : 'badge-info')) }}">
                                    {{ ucfirst($fine->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-gray-500">{{ $fine->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">
                                @can('manage-fines')
                                    @if($fine->status === 'pending')
                                        <button wire:click="confirmPay({{ $fine->id }})" class="text-sm btn-primary py-1 px-2">Pay</button>
                                        <button wire:click="waive({{ $fine->id }})" wire:confirm="Are you sure you want to waive this fine?" class="text-sm btn-secondary py-1 px-2 ml-1">Waive</button>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">No fines found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $fines->links() }}</div>

    @can('manage-fines')
        <x-bottom-sheet name="pay-fine" :show="$showPayModal" title="Record Payment" maxWidth="md">
            <div class="p-5 space-y-4">
                <div>
                    <label class="label">Payment Method</label>
                    <select wire:model="paymentMethod" class="input-field">
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="label">Reference (optional)</label>
                    <input type="text" wire:model="reference" class="input-field" placeholder="Receipt/transaction ref">
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2 mobile-form-actions">
                    <button wire:click="$set('showPayModal', false)" x-on:click="$dispatch('close-bottom-sheet', 'pay-fine')" class="btn-outline">Cancel</button>
                    <button wire:click="pay" class="btn-primary">Confirm Payment</button>
                </div>
            </div>
        </x-bottom-sheet>
    @endcan
</div>
