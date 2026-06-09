@section('title', 'Invoices')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Invoices</h1>

    <div class="card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search invoice or member..."
                       class="input-field w-full">
            </div>
            <div>
                <select wire:model.live="status" class="input-field w-full">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <select wire:model.live="type" class="input-field w-full">
                    <option value="">All Types</option>
                    <option value="fine">Fine</option>
                    <option value="registration_fee">Registration Fee</option>
                    <option value="donation">Donation</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Amount (KES)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Issued</th>
                        <th class="py-3 px-4">Due</th>
                        <th class="py-3 px-4">Issued By</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr class="border-t dark:border-gray-700">
                            <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $invoice->invoice_number }}</td>
                            <td class="py-3 px-4 text-gray-800 dark:text-gray-200">{{ $invoice->user?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4"><span class="badge">{{ ucwords(str_replace('_', ' ', $invoice->type)) }}</span></td>
                            <td class="py-3 px-4 text-right font-medium text-gray-800 dark:text-gray-200">{{ number_format($invoice->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="badge {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'overdue' ? 'badge-danger' : ($invoice->status === 'cancelled' ? 'badge-secondary' : 'badge-warning')) }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-gray-500">{{ $invoice->issued_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $invoice->due_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $invoice->issuer?->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('finance.invoice.download', $invoice) }}"
                                       class="btn-outline btn-sm p-1" title="Download PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                    @can('generate-invoices')
                                        <a href="{{ route('finance.invoice.email', $invoice) }}"
                                           class="btn-outline btn-sm p-1" title="Email PDF"
                                           onclick="return confirm('Send invoice via email?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-gray-400">No invoices found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $invoices->links() }}</div>
</div>
