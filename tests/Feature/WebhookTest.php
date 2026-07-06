<?php

namespace Tests\Feature;

use App\Modules\Finance\Services\MpesaService;
use App\Modules\Subscriptions\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_mpesa_callback_returns_success(): void
    {
        $mock = $this->createMock(MpesaService::class);
        $mock->expects($this->once())
            ->method('processCallback')
            ->with($this->callback(fn ($data) => ($data['Body']['stkCallback']['ResultCode'] ?? null) === 0));

        $this->app->instance(MpesaService::class, $mock);

        $payload = [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_270720251234567890',
                    'MerchantRequestID' => '29115-34638961-1',
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 500],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'MPS123ABC'],
                            ['Name' => 'PhoneNumber', 'Value' => '254712345678'],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/mpesa/callback', $payload);

        $response->assertOk()
            ->assertJson(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function test_mpesa_callback_handles_failure(): void
    {
        $mock = $this->createMock(MpesaService::class);
        $mock->expects($this->once())
            ->method('processCallback')
            ->with($this->callback(fn ($data) => ($data['Body']['stkCallback']['ResultCode'] ?? null) === 1));

        $this->app->instance(MpesaService::class, $mock);

        $payload = [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_270720251234567891',
                    'ResultCode' => 1,
                    'ResultDesc' => 'The balance is insufficient for the transaction.',
                ],
            ],
        ];

        $response = $this->postJson('/api/mpesa/callback', $payload);

        $response->assertOk()
            ->assertJson(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function test_mpesa_validation_accepts_valid_request(): void
    {
        $mock = $this->createMock(MpesaService::class);
        $mock->expects($this->once())
            ->method('handleValidation')
            ->willReturn(['ResultCode' => 0, 'ResultDesc' => 'Success']);

        $this->app->instance(MpesaService::class, $mock);

        $payload = [
            'TransactionType' => 'Pay Bill',
            'TransID' => 'MPS123ABC',
            'TransAmount' => 500,
            'BusinessShortCode' => '174379',
            'MSISDN' => '254712345678',
        ];

        $response = $this->postJson('/api/mpesa/validation', $payload);

        $response->assertOk()
            ->assertJson(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function test_mpesa_validation_rejects_invalid_amount(): void
    {
        $mock = $this->createMock(MpesaService::class);
        $mock->expects($this->once())
            ->method('handleValidation')
            ->willReturn(['ResultCode' => 1, 'ResultDesc' => 'Invalid transaction amount']);

        $this->app->instance(MpesaService::class, $mock);

        $payload = [
            'TransactionType' => 'Pay Bill',
            'TransID' => 'MPS123ABC',
            'TransAmount' => 0,
            'BusinessShortCode' => '174379',
            'MSISDN' => '254712345678',
        ];

        $response = $this->postJson('/api/mpesa/validation', $payload);

        $response->assertOk()
            ->assertJson(['ResultCode' => 1]);
    }

    public function test_stripe_webhook_returns_error_when_secret_not_configured(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $response = $this->postJson('/api/stripe/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_123']],
        ], ['Stripe-Signature' => 'fake_sig']);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Webhook secret not configured']);
    }

    public function test_stripe_webhook_processes_event(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

        $mock = $this->createMock(StripeService::class);
        $mock->expects($this->once())
            ->method('handleWebhook')
            ->willReturn(['success' => true, 'message' => 'Event handled']);

        $this->app->instance(StripeService::class, $mock);

        $response = $this->postJson('/api/stripe/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_123']],
        ], ['Stripe-Signature' => 't=1492774579,v1=valid_signature']);

        $response->assertOk()
            ->assertJson(['status' => 'success']);
    }

    public function test_stripe_webhook_returns_error_on_failure(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

        $mock = $this->createMock(StripeService::class);
        $mock->expects($this->once())
            ->method('handleWebhook')
            ->willReturn(['success' => false, 'error' => 'Invalid signature']);

        $this->app->instance(StripeService::class, $mock);

        $response = $this->postJson('/api/stripe/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_123']],
        ], ['Stripe-Signature' => 'bad_signature']);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid signature']);
    }
}
