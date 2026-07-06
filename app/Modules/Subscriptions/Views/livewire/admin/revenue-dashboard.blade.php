<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Revenue Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">KES {{ number_format($revenueStats['total_revenue'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Month</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">KES {{ number_format($revenueStats['current_month_revenue'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Growth Rate</p>
                <p class="text-2xl font-bold @if(($revenueStats['monthly_growth_rate'] ?? 0) >= 0) text-green-600 @else text-red-600 @endif">
                    {{ ($revenueStats['monthly_growth_rate'] ?? 0) >= 0 ? '+' : '' }}{{ $revenueStats['monthly_growth_rate'] ?? 0 }}%
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transactions</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $revenueStats['total_transactions'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-white">Subscription Status</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach(['active' => 'green', 'trial' => 'blue', 'expired' => 'red', 'suspended' => 'yellow', 'cancelled' => 'gray', 'pending' => 'orange'] as $status => $color)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ $status }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $subscriptionStats[$status] ?? 0 }}</span>
                            </div>
                        @endforeach
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Total</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $subscriptionStats['total'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-white">Revenue by Plan</h3>
                </div>
                <div class="p-6">
                    @if(!empty($revenueStats['revenue_by_plan']))
                        <div class="space-y-3">
                            @foreach($revenueStats['revenue_by_plan'] as $plan)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $plan['name'] }}</span>
                                    <div class="text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">KES {{ number_format($plan['total'], 2) }}</span>
                                        <span class="text-xs text-gray-500 ml-2">({{ $plan['count'] }} txns)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No revenue data available.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-900 dark:text-white">Plans Overview</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscribers</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($planStats as $plan)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $plan['name'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">KES {{ number_format($plan['price'], 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ $plan['billing_cycle'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $plan['subscribers_count'] }}</td>
                                <td class="px-4 py-3">
                                    @if($plan['is_active'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No plans created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
