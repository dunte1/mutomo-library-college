<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">All Subscriptions</h1>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" wire:model.live="search" placeholder="Search by user name..." class="input-field">
                    <select wire:model.live="status" class="input-field">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="suspended">Suspended</option>
                        <option value="pending">Pending</option>
                        <option value="trial">Trial</option>
                    </select>
                    <select wire:model.live="billingCycle" class="input-field">
                        <option value="">All Cycles</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $subscription->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $subscription->user->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $subscription->plan->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $subscription->isActive() ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                    {{ $subscription->isSuspended() ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                                    {{ $subscription->isExpired() ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                    {{ $subscription->isCancelled() ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400' : '' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ $subscription->billing_cycle }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $subscription->start_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $subscription->end_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($subscription->isActive())
                                        <button wire:click="suspendSubscription({{ $subscription->id }})" class="text-sm text-yellow-600 hover:text-yellow-800">Suspend</button>
                                        <button wire:click="cancelSubscription({{ $subscription->id }})" wire:confirm="Cancel this subscription?" class="text-sm text-red-600 hover:text-red-800">Cancel</button>
                                    @elseif($subscription->isSuspended())
                                        <button wire:click="activateSubscription({{ $subscription->id }})" class="text-sm text-green-600 hover:text-green-800">Activate</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No subscriptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
</div>
