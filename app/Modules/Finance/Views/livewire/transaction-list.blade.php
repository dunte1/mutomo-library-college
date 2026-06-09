@section('title', 'Transactions')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Transactions</h1>

    <div class="card p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end justify-between">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search transactions..."
                           class="input-field w-full">
                </div>
                <div>
                    <select wire:model.live="type" class="input-field w-full">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}">{{ str_replace('_', ' ', ucwords($type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="paymentMethod" class="input-field w-full">
                        <option value="">All Methods</option>
                        @foreach($methods as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="status" class="input-field w-full">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
            <div class="shrink-0">
                <button wire:click="exportCsv" class="btn-outline text-sm">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-3 px-4">Transaction #</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4">Reference</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr class="border-t dark:border-gray-700">
                            <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $txn->transaction_number }}</td>
                            <td class="py-3 px-4 text-gray-800 dark:text-gray-200">{{ $txn->user?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4"><span class="badge">{{ str_replace('_', ' ', $txn->type) }}</span></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $txn->payment_method ?? '-' }}</td>
                            <td class="py-3 px-4 text-right font-medium text-gray-800 dark:text-gray-200">KES {{ number_format($txn->amount, 2) }}</td>
                            <td class="py-3 px-4 text-xs text-gray-500">{{ $txn->reference ?? '-' }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $txn->paid_at?->format('d/m/Y') ?? $txn->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">
                                <span class="badge {{ $txn->status === 'completed' ? 'badge-success' : ($txn->status === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $txn->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($txn->receipt || $txn->invoice || $txn->status === 'completed')
                                    <a href="{{ route('finance.receipt', $txn->id) }}" wire:navigate class="btn-sm btn-outline text-xs">
                                        Receipt
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-gray-400">No transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
