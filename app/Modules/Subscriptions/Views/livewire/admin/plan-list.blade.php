<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Plans</h1>
            <a href="{{ route('admin.subscriptions.plans.create') }}" class="btn-primary">Create Plan</a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" wire:model.live="search" placeholder="Search plans..." class="input-field">
                    <select wire:model.live="type" class="input-field">
                        <option value="">All Types</option>
                        <option value="individual">Individual</option>
                        <option value="school">School</option>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscribers</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($plans as $plan)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ $plan->type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ $plan->billing_cycle ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">KES {{ number_format($plan->price, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $plan->subscribers_count ?? 0 }}</td>
                            <td class="px-4 py-3">
                                @if($plan->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">Edit</a>
                                    <button wire:click="deletePlan({{ $plan->id }})" wire:confirm="Delete this plan?" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No plans found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $plans->links() }}
            </div>
        </div>
    </div>
</div>
