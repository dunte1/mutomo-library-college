<div>
    @if($transaction)
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $showInvoice ? 'Invoice' : 'Receipt' }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $showInvoice ? $transaction->invoice?->invoice_number : $transaction->receipt?->receipt_number }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="toggleView" class="btn-outline btn-sm">
                    {{ $showInvoice ? 'View Receipt' : 'View Invoice' }}
                </button>
                @if(!$showInvoice && $transaction->receipt)
                    <a href="{{ route('finance.receipt.download', $transaction->receipt) }}" class="btn-outline btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PDF
                    </a>
                @endif
                @if($showInvoice && $transaction->invoice)
                    <a href="{{ route('finance.invoice.download', $transaction->invoice) }}" class="btn-outline btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PDF
                    </a>
                @endif
                <button onclick="window.print()" class="btn-outline btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ config('app.name') }}
                    </h1>
                    @if(config('app.library_address'))
                        <p class="text-sm text-gray-500 mt-1">{{ config('app.library_address') }}</p>
                    @endif
                    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mt-4">
                        {{ $showInvoice ? 'INVOICE' : 'OFFICIAL RECEIPT' }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ $showInvoice
                            ? ($transaction->invoice?->invoice_number ?? 'N/A')
                            : ($transaction->receipt?->receipt_number ?? 'N/A') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Issued To</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $transaction->user?->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $transaction->user?->email }}</p>
                        @if($transaction->user?->phone)
                            <p class="text-sm text-gray-500">{{ $transaction->user->phone }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $showInvoice ? 'Invoice Date' : 'Receipt Date' }}</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100 mt-1">
                            {{ $showInvoice
                                ? ($transaction->invoice?->issued_at?->format('d M Y H:i') ?? $transaction->paid_at?->format('d M Y H:i') ?? '—')
                                : ($transaction->receipt?->issued_at?->format('d M Y H:i') ?? $transaction->paid_at?->format('d M Y H:i') ?? '—') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2 uppercase tracking-wide">Transaction</p>
                        <p class="text-sm font-mono text-gray-600 dark:text-gray-400">{{ $transaction->transaction_number }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                <table class="w-full mb-8">
                    <thead>
                        <tr class="border-t border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-700/50">
                            <td class="py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $transaction->description ?? str_replace('_', ' ', $transaction->type) }}
                                @if($transaction->fine?->borrowRecord?->bookCopy?->book)
                                    <br>
                                    <span class="text-xs text-gray-500">
                                        Book: {{ $transaction->fine->borrowRecord->bookCopy->book->title }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($transaction->amount, 2) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Total</th>
                            <th class="py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                KES {{ number_format($transaction->amount, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
                </div>

                <div class="text-center text-xs text-gray-400 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <p>{{ config('app.name') }} — {{ config('app.library_address') ?? 'Library Management System' }}</p>
                    @if(config('app.library_phone'))
                        <p class="mt-1">Tel: {{ config('app.library_phone') }} | Email: {{ config('app.library_email') ?? '' }}</p>
                    @endif
                    <p class="mt-1">Transaction #{{ $transaction->transaction_number }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center gap-4 mt-6">
            <a href="{{ route('finance.receipts') }}" wire:navigate class="btn-outline">
                Back to Receipts
            </a>
        </div>
    </div>
    @else
    <div class="max-w-4xl mx-auto">
        <x-header title="Receipts" subtitle="Browse and search issued receipts" />

        <div class="mb-4">
            <x-input icon="search" placeholder="Search by receipt number or patron name..."
                wire:model.live.debounce="search" />
        </div>

        <x-card>
            <x-table>
                <x-thead>
                    <x-tr>
                        <x-th>Receipt #</x-th>
                        <x-th>Patron</x-th>
                        <x-th>Amount</x-th>
                        <x-th>Date</x-th>
                        <x-th></x-th>
                    </x-tr>
                </x-thead>
                <x-tbody>
                    @forelse($receipts as $r)
                        <x-tr wire:key="{{ $r->id }}">
                            <x-td>
                                <span class="font-mono text-sm">{{ $r->receipt_number }}</span>
                            </x-td>
                            <x-td>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $r->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $r->user?->email ?? '' }}</div>
                            </x-td>
                            <x-td>
                                <span class="font-medium">KES {{ number_format($r->amount, 2) }}</span>
                            </x-td>
                            <x-td>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $r->issued_at?->format('d/m/Y') ?? $r->created_at->format('d/m/Y') }}</span>
                            </x-td>
                            <x-td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('finance.receipt.download', $r) }}"
                                       class="btn-outline btn-sm p-1" title="Download PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('finance.receipt', $r->transaction_id) }}" wire:navigate class="btn-outline btn-sm">
                                        View
                                    </a>
                                </div>
                            </x-td>
                        </x-tr>
                    @empty
                        <x-tr>
                            <x-td colspan="5">
                                <x-empty-state icon="receipt" title="No receipts found"
                                    description="Receipts will appear here once payments are processed." />
                            </x-td>
                        </x-tr>
                    @endforelse
                </x-tbody>
            </x-table>
        </x-card>

        <div class="mt-4">
            {{ $receipts->links() }}
        </div>
    </div>
    @endif
</div>
