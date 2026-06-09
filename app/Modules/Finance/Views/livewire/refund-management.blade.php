@section('title', 'Refunds')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Refund Management</h1>

    <div class="card p-4">
        <div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search transaction or member..."
                   class="input-field w-full max-w-md">
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-3 px-4">Transaction #</th>
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Amount (KES)</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Paid At</th>
                        <th class="py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr class="border-t dark:border-gray-700">
                            <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $txn->transaction_number }}</td>
                            <td class="py-3 px-4 text-gray-800 dark:text-gray-200">{{ $txn->user?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4"><span class="badge">{{ str_replace('_', ' ', $txn->type) }}</span></td>
                            <td class="py-3 px-4 text-right font-medium text-gray-800 dark:text-gray-200">{{ number_format($txn->amount, 2) }}</td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $txn->payment_method ?? '-' }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $txn->paid_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-3 px-4">
                                @if($txn->status === 'completed')
                                    <button wire:click="confirmRefund({{ $txn->id }})" class="text-sm btn-secondary py-1 px-2">Refund</button>
                                @elseif($txn->status === 'refunded')
                                    <span class="badge badge-warning">Refunded</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">No completed transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $transactions->links() }}</div>

    <x-bottom-sheet name="refund" :show="$showRefundModal" title="Process Refund" maxWidth="md">
        <div class="p-5 space-y-4">
            <div>
                <label class="label">Reason for Refund</label>
                <textarea wire:model="refundReason" class="input-field w-full" rows="3" placeholder="Explain why this payment is being refunded..."></textarea>
                @error('refundReason') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2 mobile-form-actions">
                <button wire:click="$set('showRefundModal', false)" x-on:click="$dispatch('close-bottom-sheet', 'refund')" class="btn-outline">Cancel</button>
                <button wire:click="processRefund" class="btn-primary">Process Refund</button>
            </div>
        </div>
    </x-bottom-sheet>
</div>
