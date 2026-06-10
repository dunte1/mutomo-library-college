<?php

namespace App\Modules\Subscriptions\Services;

use App\Mail\PaymentConfirmation;
use App\Mail\SubscriptionActivation;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Services\BillingService;
use App\Modules\Finance\Services\FinanceService;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use App\Modules\Subscriptions\Models\WebhookLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\WebhookEndpoint;

class StripeService
{
    protected bool $initialized = false;

    public function __construct()
    {
        $this->init();
    }

    protected function init(): void
    {
        $key = config('services.stripe.secret_key');
        if ($key) {
            Stripe::setApiKey($key);
            Stripe::setApiVersion('2025-02-24');
            $this->initialized = true;
        }
    }

    public function createCheckoutSession(Plan $plan, Subscription $subscription, string $successUrl, string $cancelUrl): ?Session
    {
        if (!$this->initialized) {
            Log::error('Stripe not configured');
            return null;
        }

        try {
            $session = Session::create([
                'mode' => 'subscription',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($plan->currency),
                            'product_data' => [
                                'name' => $plan->name,
                                'description' => $plan->description,
                            ],
                            'unit_amount' => (int) round($plan->price * 100),
                            'recurring' => [
                                'interval' => $plan->isMonthly() ? 'month' : 'year',
                            ],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'metadata' => [
                    'subscription_id' => (string) $subscription->id,
                    'plan_id' => (string) $plan->id,
                    'user_id' => (string) $subscription->user_id,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string) $subscription->user_id,
            ]);

            $subscription->update([
                'payment_gateway_subscription_id' => $session->id,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'stripe_session_id' => $session->id,
                ]),
            ]);

            return $session;
        } catch (Exception $e) {
            Log::error('Stripe checkout session error: ' . $e->getMessage());
            return null;
        }
    }

    public function createOneTimeCheckoutSession(Plan $plan, Subscription $subscription, string $successUrl, string $cancelUrl): ?Session
    {
        if (!$this->initialized) {
            Log::error('Stripe not configured');
            return null;
        }

        try {
            $session = Session::create([
                'mode' => 'payment',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($plan->currency),
                            'product_data' => [
                                'name' => $plan->name,
                                'description' => $plan->description . ' (One-time payment)',
                            ],
                            'unit_amount' => (int) round($plan->price * 100),
                        ],
                        'quantity' => 1,
                    ],
                ],
                'metadata' => [
                    'subscription_id' => (string) $subscription->id,
                    'plan_id' => (string) $plan->id,
                    'user_id' => (string) $subscription->user_id,
                    'payment_type' => 'subscription_one_time',
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string) $subscription->user_id,
            ]);

            $subscription->update([
                'payment_gateway_subscription_id' => $session->id,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'stripe_session_id' => $session->id,
                ]),
            ]);

            return $session;
        } catch (Exception $e) {
            Log::error('Stripe one-time checkout error: ' . $e->getMessage());
            return null;
        }
    }

    public function handleWebhook(string $payload, string $sigHeader, string $webhookSecret): array
    {
        $event = null;

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Invalid signature'];
        } catch (Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }

        WebhookLog::create([
            'gateway' => 'stripe',
            'event_type' => $event->type,
            'payload' => json_decode($payload, true),
            'status' => 'pending',
        ]);

        try {
            $result = match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
                'invoice.paid' => $this->handleInvoicePaid($event->data->object),
                'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event->data->object),
                'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
                default => ['success' => true, 'message' => 'Unhandled event type: ' . $event->type],
            };

            WebhookLog::where('event_type', $event->type)
                ->latest()
                ->first()
                ?->update(['status' => 'processed', 'processed_at' => now()]);

            return $result;
        } catch (Exception $e) {
            WebhookLog::where('event_type', $event->type)
                ->latest()
                ->first()
                ?->update(['status' => 'failed', 'error' => $e->getMessage()]);

            Log::error('Stripe webhook handler error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function handleCheckoutCompleted(object $session): array
    {
        $subscriptionId = $session->metadata->subscription_id ?? null;
        if (!$subscriptionId) {
            return ['success' => false, 'error' => 'No subscription_id in metadata'];
        }

        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            return ['success' => false, 'error' => 'Subscription not found'];
        }

        $paymentIntent = $session->payment_intent;
        $amount = ($session->amount_total ?? 0) / 100;

        $transaction = Transaction::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'transaction_number' => Transaction::generateNumber(),
            'type' => 'subscription_payment',
            'payment_method' => 'stripe',
            'amount' => $amount,
            'currency' => strtoupper($session->currency ?? 'KES'),
            'reference' => $paymentIntent,
            'description' => "Stripe payment for {$subscription->plan->name}",
            'status' => 'completed',
            'paid_at' => now(),
            'recorded_by' => null,
        ]);

        $subscription->update([
            'status' => 'active',
            'payment_method' => 'stripe',
            'payment_gateway_subscription_id' => $session->subscription ?? $session->id,
            'metadata' => array_merge($subscription->metadata ?? [], [
                'stripe_payment_intent' => $paymentIntent,
            ]),
        ]);

        // Generate invoice
        try {
            $financeService = app(FinanceService::class);
            $invoice = $financeService->generateInvoice(
                $subscription->user,
                $amount,
                'subscription',
                "Stripe subscription payment: {$subscription->plan->name}"
            );
            $invoice->update(['transaction_id' => $transaction->id]);
        } catch (\Throwable $e) {
            Log::warning("Stripe: Failed to generate invoice: {$e->getMessage()}");
        }

        // Generate receipt
        try {
            $receipt = $financeService->generateReceipt($transaction);
        } catch (\Throwable $e) {
            Log::warning("Stripe: Failed to generate receipt: {$e->getMessage()}");
        }

        // Auto-issue library card
        try {
            $member = Member::where('user_id', $subscription->user_id)->first();
            if ($member && !$member->libraryCard) {
                app(LibraryCardService::class)->autoIssueCard($member);
            }
        } catch (\Throwable $e) {
            Log::warning("Stripe: Failed to auto-issue library card: {$e->getMessage()}");
        }

        // Send emails
        try {
            $user = $subscription->user;
            if ($user && $user->email) {
                Mail::to($user->email)->queue(new SubscriptionActivation($subscription));
                Mail::to($user->email)->queue(new PaymentConfirmation($transaction, 'stripe'));
            }
        } catch (\Throwable $e) {
            Log::warning("Stripe: Failed to send notification emails: {$e->getMessage()}");
        }

        // Email receipt
        try {
            if ($transaction->receipt) {
                app(BillingService::class)->emailReceipt($transaction->receipt);
            }
        } catch (\Throwable $e) {
            Log::warning("Stripe: Failed to email receipt: {$e->getMessage()}");
        }

        return ['success' => true, 'message' => 'Subscription activated via checkout'];
    }

    protected function handleInvoicePaid(object $invoice): array
    {
        $subscriptionId = $invoice->subscription;
        if (!$subscriptionId) {
            return ['success' => false, 'error' => 'No subscription in invoice'];
        }

        $subscription = Subscription::where('payment_gateway_subscription_id', $subscriptionId)->first();
        if (!$subscription) {
            return ['success' => false, 'error' => 'Subscription not found'];
        }

        $subscription->update(['status' => 'active']);

        return ['success' => true, 'message' => 'Invoice paid, subscription active'];
    }

    protected function handleInvoicePaymentFailed(object $invoice): array
    {
        $subscriptionId = $invoice->subscription;
        if (!$subscriptionId) {
            return ['success' => false, 'error' => 'No subscription in invoice'];
        }

        $subscription = Subscription::where('payment_gateway_subscription_id', $subscriptionId)->first();
        if ($subscription) {
            $subscription->update([
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'stripe_payment_failed' => true,
                    'stripe_failure_message' => $invoice->last_payment_error?->message ?? 'Unknown',
                ]),
            ]);
        }

        return ['success' => true, 'message' => 'Payment failure recorded'];
    }

    protected function handleSubscriptionUpdated(object $stripeSubscription): array
    {
        $subscription = Subscription::where('payment_gateway_subscription_id', $stripeSubscription->id)->first();
        if (!$subscription) {
            return ['success' => false, 'error' => 'Subscription not found'];
        }

        $status = match ($stripeSubscription->status) {
            'active', 'trialing' => 'active',
            'past_due' => 'suspended',
            'canceled' => 'cancelled',
            'unpaid' => 'suspended',
            'incomplete' => 'pending',
            'incomplete_expired' => 'expired',
            default => $subscription->status,
        };

        if ($status !== $subscription->status) {
            $subscription->update(['status' => $status]);
        }

        return ['success' => true, 'message' => "Subscription status updated to {$status}"];
    }

    protected function handleSubscriptionDeleted(object $stripeSubscription): array
    {
        $subscription = Subscription::where('payment_gateway_subscription_id', $stripeSubscription->id)->first();
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);
        }

        return ['success' => true, 'message' => 'Subscription cancelled via Stripe'];
    }
}
