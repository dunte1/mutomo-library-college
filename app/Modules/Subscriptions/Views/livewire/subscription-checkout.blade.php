<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Complete Your Subscription</h2>
            </div>

            <div class="p-6">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">KES {{ number_format($plan->price, 2) }}<span class="text-sm text-gray-500 font-normal">/{{ $plan->billing_cycle }}</span></p>
                    @if($plan->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $plan->description }}</p>
                    @endif
                </div>

                @if($checkoutRequestId)
                    <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-6">
                        <p class="text-green-800 dark:text-green-200 font-medium">STK Push Sent!</p>
                        <p class="text-sm text-green-700 dark:text-green-300 mt-1">Check your phone to complete the M-Pesa payment.</p>
                        <button wire:click="checkPaymentStatus" class="mt-3 text-sm text-blue-600 dark:text-blue-400 underline">Check Payment Status</button>
                    </div>
                @elseif($stripeUrl)
                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                        <p class="text-blue-800 dark:text-blue-200 font-medium">Redirecting to Stripe...</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">You are being redirected to Stripe's secure checkout page.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button wire:click="$set('paymentMethod', 'mpesa')" class="p-3 border rounded-lg text-center @if($paymentMethod === 'mpesa') border-blue-500 bg-blue-50 dark:bg-blue-900/30 dark:border-blue-500 @else border-gray-300 dark:border-gray-600 @endif">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white">M-Pesa</span>
                                    <span class="text-xs text-gray-500">Mobile Money</span>
                                </button>
                                <button wire:click="$set('paymentMethod', 'stripe')" class="p-3 border rounded-lg text-center @if($paymentMethod === 'stripe') border-blue-500 bg-blue-50 dark:bg-blue-900/30 dark:border-blue-500 @else border-gray-300 dark:border-gray-600 @endif">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white">Stripe</span>
                                    <span class="text-xs text-gray-500">Card Payment</span>
                                </button>
                            </div>
                        </div>

                        @if($paymentMethod === 'mpesa')
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">M-Pesa Phone Number</label>
                                <input type="text" id="phone" wire:model="phone" placeholder="0712345678" class="mt-1 block w-full input-field">
                                @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <button wire:click="payWithMpesa" wire:loading.attr="disabled" class="w-full btn-primary flex items-center justify-center gap-2">
                                <span wire:loading.remove>Pay KES {{ number_format($plan->price, 2) }} with M-Pesa</span>
                                <span wire:loading>Processing...</span>
                            </button>
                        @endif

                        @if($paymentMethod === 'stripe')
                            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                                <p class="text-blue-800 dark:text-blue-200 text-sm mb-3">
                                    You will be redirected to Stripe's secure checkout page to pay with your credit/debit card.
                                </p>
                                <button wire:click="payWithStripe" wire:loading.attr="disabled" class="w-full btn-primary flex items-center justify-center gap-2">
                                    <span wire:loading.remove>Pay KES {{ number_format($plan->price, 2) }} with Card</span>
                                    <span wire:loading>Redirecting to Stripe...</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
