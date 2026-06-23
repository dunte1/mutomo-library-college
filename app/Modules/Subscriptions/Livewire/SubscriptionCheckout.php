<?php

namespace App\Modules\Subscriptions\Livewire;

use App\Modules\Finance\Services\MpesaService;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Services\StripeService;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Livewire\Component;

class SubscriptionCheckout extends Component
{
    public Plan $plan;

    public string $paymentMethod = 'mpesa';

    public string $phone = '';

    public bool $processing = false;

    public ?string $checkoutRequestId = null;

    public ?int $transactionId = null;

    public ?string $stripeUrl = null;

    protected function rules(): array
    {
        return [
            'paymentMethod' => ['required', 'in:mpesa,stripe'],
            'phone' => ['required_if:paymentMethod,mpesa', 'string', 'regex:/^(0|254|\+254)?[17]\d{8}$/'],
        ];
    }

    public function mount(Plan $plan): void
    {
        if (! $plan->is_active) {
            abort(404);
        }

        $this->plan = $plan->loadMissing('subscriptions');

        if (auth()->user()->activeSubscription) {
            session()->flash('error', 'You already have an active subscription.');
        }
    }

    public function payWithMpesa(): void
    {
        $this->validate();

        if (auth()->user()->activeSubscription) {
            $this->dispatch('notify', message: 'You already have an active subscription.', type: 'error');

            return;
        }

        $this->processing = true;

        try {
            $subscriptionService = app(SubscriptionService::class);
            $subscription = $subscriptionService->createSubscription(auth()->user(), $this->plan, [
                'status' => 'pending',
                'payment_method' => 'mpesa',
            ]);

            $mpesaService = app(MpesaService::class);
            $result = $mpesaService->stkPush(
                (float) $this->plan->price,
                $this->phone,
                "Subscription: {$this->plan->name}",
                auth()->id()
            );

            if ($result['success']) {
                $this->checkoutRequestId = $result['checkout_request_id'];
                $this->transactionId = $result['mpesa_transaction_id'];

                $this->dispatch('notify', message: 'STK push sent. Check your phone to complete payment.', type: 'success');
            } else {
                $subscription->cancel('M-Pesa payment failed');
                $this->dispatch('notify', message: $result['message'], type: 'error');
                $this->processing = false;
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Payment failed. Please try again.', type: 'error');
            $this->processing = false;
        }
    }

    public function payWithStripe(): void
    {
        if (auth()->user()->activeSubscription) {
            $this->dispatch('notify', message: 'You already have an active subscription.', type: 'error');

            return;
        }

        $this->processing = true;

        try {
            $subscriptionService = app(SubscriptionService::class);
            $subscription = $subscriptionService->createSubscription(auth()->user(), $this->plan, [
                'status' => 'pending',
                'payment_method' => 'stripe',
            ]);

            $stripeService = app(StripeService::class);
            $successUrl = route('subscriptions.my', [], true);
            $cancelUrl = route('subscriptions.checkout', ['plan' => $this->plan->id], true);

            $session = $stripeService->createOneTimeCheckoutSession(
                $this->plan,
                $subscription,
                $successUrl,
                $cancelUrl
            );

            if ($session && $session->url) {
                $this->stripeUrl = $session->url;
                $this->redirect($session->url);
            } else {
                $subscription->cancel('Stripe checkout creation failed');
                $this->dispatch('notify', message: 'Could not create Stripe checkout session.', type: 'error');
                $this->processing = false;
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Stripe payment setup failed.', type: 'error');
            $this->processing = false;
        }
    }

    public function checkPaymentStatus(): void
    {
        $this->dispatch('notify', message: 'If you completed payment, your subscription will be activated shortly.', type: 'info');
    }

    public function render()
    {
        return view('subscriptions::livewire.subscription-checkout', [
            'user' => auth()->user(),
        ])->layout('layouts.app');
    }
}
