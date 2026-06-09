<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Subscription Plans</h1>
            <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">Choose the plan that works best for you or your institution</p>
        </div>

        @if($activeSubscription)
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-8">
                <p class="text-blue-800 dark:text-blue-200">
                    You have an active <strong>{{ $activeSubscription->plan->name }}</strong> subscription.
                    <a href="{{ route('subscriptions.my') }}" class="underline font-medium">View My Subscription</a>
                </p>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Individual Plans</h2>
                <div class="grid gap-6">
                    @if($individualMonthly)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $individualMonthly->name }}</h3>
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">KES {{ number_format($individualMonthly->price, 2) }}<span class="text-sm text-gray-500 font-normal">/month</span></p>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $individualMonthly->description }}</p>
                            @if($individualMonthly->features)
                                <ul class="mt-4 space-y-2">
                                    @foreach($individualMonthly->features as $feature)
                                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <button wire:click="subscribe({{ $individualMonthly->id }})" @if($activeSubscription) disabled @endif class="mt-6 w-full btn-primary @if($activeSubscription) opacity-50 cursor-not-allowed @endif">
                                @if($activeSubscription) Already Subscribed @else Subscribe Now @endif
                            </button>
                        </div>
                    @endif

                    @if($individualYearly)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 ring-2 ring-blue-500">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $individualYearly->name }}</h3>
                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-semibold px-3 py-1 rounded-full">Best Value</span>
                            </div>
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">KES {{ number_format($individualYearly->price, 2) }}<span class="text-sm text-gray-500 font-normal">/year</span></p>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $individualYearly->description }}</p>
                            @if($individualYearly->features)
                                <ul class="mt-4 space-y-2">
                                    @foreach($individualYearly->features as $feature)
                                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <button wire:click="subscribe({{ $individualYearly->id }})" @if($activeSubscription) disabled @endif class="mt-6 w-full btn-primary @if($activeSubscription) opacity-50 cursor-not-allowed @endif">
                                @if($activeSubscription) Already Subscribed @else Subscribe Now @endif
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">School Plans</h2>
                <div class="grid gap-6">
                    @if($schoolMonthly)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $schoolMonthly->name }}</h3>
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">KES {{ number_format($schoolMonthly->price, 2) }}<span class="text-sm text-gray-500 font-normal">/month</span></p>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $schoolMonthly->description }}</p>
                            @if($schoolMonthly->features)
                                <ul class="mt-4 space-y-2">
                                    @foreach($schoolMonthly->features as $feature)
                                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <button wire:click="subscribe({{ $schoolMonthly->id }})" @if($activeSubscription) disabled @endif class="mt-6 w-full btn-secondary @if($activeSubscription) opacity-50 cursor-not-allowed @endif">
                                @if($activeSubscription) Already Subscribed @else Subscribe Now @endif
                            </button>
                        </div>
                    @endif

                    @if($schoolYearly)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 ring-2 ring-purple-500">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $schoolYearly->name }}</h3>
                                <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs font-semibold px-3 py-1 rounded-full">Best Value</span>
                            </div>
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">KES {{ number_format($schoolYearly->price, 2) }}<span class="text-sm text-gray-500 font-normal">/year</span></p>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $schoolYearly->description }}</p>
                            @if($schoolYearly->features)
                                <ul class="mt-4 space-y-2">
                                    @foreach($schoolYearly->features as $feature)
                                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <button wire:click="subscribe({{ $schoolYearly->id }})" @if($activeSubscription) disabled @endif class="mt-6 w-full btn-secondary @if($activeSubscription) opacity-50 cursor-not-allowed @endif">
                                @if($activeSubscription) Already Subscribed @else Subscribe Now @endif
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
