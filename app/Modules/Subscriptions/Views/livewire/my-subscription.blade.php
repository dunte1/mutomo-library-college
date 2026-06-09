<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Subscriptions</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Manage your subscription plans</p>
        </div>

        @if($subscriptions->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">You don't have any subscriptions yet.</p>
                <a href="{{ route('subscriptions.plans') }}" class="btn-primary inline-block">View Plans</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($subscriptions as $subscription)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $subscription->plan->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($subscription->isActive())
                                        Active until {{ $subscription->end_date?->format('d M Y') ?? 'N/A' }}
                                    @elseif($subscription->isExpired())
                                        Expired on {{ $subscription->end_date?->format('d M Y') ?? 'N/A' }}
                                    @elseif($subscription->isCancelled())
                                        Cancelled on {{ $subscription->cancelled_at?->format('d M Y') ?? 'N/A' }}
                                    @elseif($subscription->isSuspended())
                                        Suspended
                                    @elseif($subscription->isPending())
                                        Pending Payment
                                    @elseif($subscription->isOnTrial())
                                        Trial until {{ $subscription->trial_ends_at?->format('d M Y') ?? 'N/A' }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                    @if($subscription->isActive()) bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @elseif($subscription->isExpired()) bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                    @elseif($subscription->isCancelled()) bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                    @elseif($subscription->isSuspended()) bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                    @elseif($subscription->isPending()) bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                    @else bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                                    @endif">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">KES {{ number_format($subscription->plan->price, 2) }}/{{ $subscription->billing_cycle }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-3">
                            @if($subscription->isActive())
                                <button wire:click="cancelSubscription({{ $subscription->id }})" wire:confirm="Are you sure you want to cancel this subscription?" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 underline">
                                    Cancel Subscription
                                </button>
                            @endif
                            @if($subscription->isPending())
                                <a href="{{ route('subscriptions.checkout', ['plan' => $subscription->plan_id]) }}" class="text-sm text-blue-600 hover:text-blue-800 underline">
                                    Complete Payment
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
