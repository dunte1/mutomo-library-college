@section('title', 'Finance')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Finance Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Today Collections</p>
            <p class="stat-value">KES {{ number_format($stats['today_collections'] ?? 0, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">This Month</p>
            <p class="stat-value">KES {{ number_format($stats['month_collections'] ?? 0, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Fine Collections</p>
            <p class="stat-value">KES {{ number_format($stats['total_fines_collected'] ?? 0, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Pending Fines</p>
            <p class="stat-value text-amber-600">{{ number_format($stats['pending_fines'] ?? 0, 2) }} KES</p>
            <p class="stat-sublabel">{{ $stats['pending_fine_count'] ?? 0 }} outstanding</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Borrow Trends (6 months)</h3>
            @if(!empty($analytics['borrow_trends']))
                <div class="flex items-end gap-3 h-32">
                    @foreach($analytics['borrow_trends'] as $month => $count)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs text-gray-500">{{ $count }}</span>
                            <div class="w-full bg-primary-200 dark:bg-primary-900/30 rounded-t"
                                 style="height: {{ $count > 0 ? ($count / max($analytics['borrow_trends']) * 100) : 1 }}%">
                                <div class="w-full h-full bg-primary-600 dark:bg-primary-500 rounded-t opacity-70"></div>
                            </div>
                            <span class="text-xs text-gray-500 truncate w-full text-center">{{ $month }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm">No data yet</p>
            @endif
        </div>

        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Fine Collection Trends</h3>
            @if(!empty($analytics['collection_trends']))
                <div class="flex items-end gap-3 h-32">
                    @foreach($analytics['collection_trends'] as $month => $amount)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs text-gray-500">KES {{ number_format($amount) }}</span>
                            <div class="w-full bg-emerald-200 dark:bg-emerald-900/30 rounded-t"
                                 style="height: {{ $amount > 0 ? ($amount / max($analytics['collection_trends']) * 100) : 1 }}%">
                                <div class="w-full h-full bg-emerald-600 dark:bg-emerald-500 rounded-t opacity-70"></div>
                            </div>
                            <span class="text-xs text-gray-500 truncate w-full text-center">{{ $month }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm">No data yet</p>
            @endif
        </div>

        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Top Borrowers</h3>
            @if(!empty($analytics['top_borrowers']))
                <ul class="space-y-2">
                    @foreach($analytics['top_borrowers'] as $i => $borrower)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $i + 1 }}. {{ $borrower['name'] }}</span>
                            <span class="badge badge-info">{{ $borrower['count'] }} borrows</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-400 text-sm">No data yet</p>
            @endif
        </div>

        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Department Usage</h3>
            @if(!empty($analytics['department_usage']))
                <ul class="space-y-2">
                    @foreach($analytics['department_usage'] as $dept)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $dept['department'] }}</span>
                            <span class="badge">{{ $dept['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-400 text-sm">No data yet</p>
            @endif
        </div>
    </div>

    <div class="card p-4">
        <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Recent Transactions</h3>
        @if($stats['recent_transactions']->isNotEmpty())
            <div class="overflow-x-auto table-mobile-cards">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b dark:border-gray-700">
                            <th class="py-2 pr-4">#</th>
                            <th class="py-2 pr-4">User</th>
                            <th class="py-2 pr-4">Type</th>
                            <th class="py-2 pr-4">Method</th>
                            <th class="py-2 pr-4 text-right">Amount</th>
                            <th class="py-2 pr-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['recent_transactions'] as $txn)
                            <tr class="border-b dark:border-gray-700/50">
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $txn->transaction_number }}</td>
                                <td class="py-2 pr-4 text-gray-800 dark:text-gray-200">{{ $txn->user?->name ?? 'N/A' }}</td>
                                <td class="py-2 pr-4">
                                    <span class="badge">{{ str_replace('_', ' ', $txn->type) }}</span>
                                </td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">{{ $txn->payment_method }}</td>
                                <td class="py-2 pr-4 text-right font-medium text-gray-800 dark:text-gray-200">KES {{ number_format($txn->amount, 2) }}</td>
                                <td class="py-2 pr-4 text-gray-500">{{ $txn->paid_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-400 text-sm">No transactions yet</p>
        @endif
    </div>
</div>
