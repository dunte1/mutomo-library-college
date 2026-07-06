<?php

namespace Tests\Feature;

use App\Modules\Finance\Models\MpesaTransaction;
use App\Modules\Finance\Services\MpesaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MpesaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MpesaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->service = app(MpesaService::class);
    }

    public function test_stk_push_returns_failure_for_zero_amount(): void
    {
        $result = $this->service->stkPush(0, '254712345678');

        $this->assertFalse($result['success']);
        $this->assertSame('Amount must be greater than zero', $result['message']);
    }

    public function test_stk_push_returns_failure_when_token_fails(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([], 401),
        ]);

        $result = $this->service->stkPush(100, '254712345678');

        $this->assertFalse($result['success']);
        $this->assertSame('M-Pesa service unavailable', $result['message']);

        $this->assertDatabaseHas('mpesa_transactions', [
            'phone_number' => '254712345678',
            'amount' => 100,
            'status' => 'failed',
        ]);
    }

    public function test_stk_push_successful(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'test-token'], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
                'MerchantRequestID' => 'mreq-001',
                'CheckoutRequestID' => 'checkout-001',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
            ], 200),
        ]);

        $result = $this->service->stkPush(100, '254712345678', 'Test Ref', 1);

        $this->assertTrue($result['success']);
        $this->assertSame('checkout-001', $result['checkout_request_id']);

        $this->assertDatabaseHas('mpesa_transactions', [
            'merchant_request_id' => 'mreq-001',
            'checkout_request_id' => 'checkout-001',
            'phone_number' => '254712345678',
            'amount' => 100,
            'user_id' => 1,
            'status' => 'pending',
        ]);
    }

    public function test_stk_push_failed_response(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'test-token'], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
                'MerchantRequestID' => 'mreq-002',
                'CheckoutRequestID' => 'checkout-002',
                'ResponseCode' => '1',
                'ResponseDescription' => 'The balance is insufficient',
            ], 200),
        ]);

        $result = $this->service->stkPush(50000, '254712345678');

        $this->assertFalse($result['success']);
        $this->assertSame('The balance is insufficient', $result['message']);

        $this->assertDatabaseHas('mpesa_transactions', [
            'status' => 'failed',
            'result_desc' => 'The balance is insufficient',
        ]);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $result = $this->service->handleValidation([
            'TransactionType' => 'PayBill',
            'TransID' => 'LIT12345',
            'TransAmount' => 200,
            'BusinessShortCode' => '174379',
            'MSISDN' => '254712345678',
        ]);

        $this->assertSame(0, $result['ResultCode']);
        $this->assertSame('Success', $result['ResultDesc']);

        $this->assertDatabaseHas('mpesa_transactions', [
            'phone_number' => '254712345678',
            'amount' => 200,
            'status' => 'validated',
        ]);
    }

    public function test_validation_rejects_zero_amount(): void
    {
        $result = $this->service->handleValidation([
            'TransactionType' => 'PayBill',
            'TransID' => 'LIT99999',
            'TransAmount' => 0,
            'BusinessShortCode' => '174379',
            'MSISDN' => '254712345678',
        ]);

        $this->assertSame(1, $result['ResultCode']);
        $this->assertSame('Invalid transaction amount', $result['ResultDesc']);
    }

    public function test_validation_rejects_invalid_shortcode(): void
    {
        $result = $this->service->handleValidation([
            'TransactionType' => 'PayBill',
            'TransID' => 'LIT88888',
            'TransAmount' => 100,
            'BusinessShortCode' => '999999',
            'MSISDN' => '254712345678',
        ]);

        $this->assertSame(1, $result['ResultCode']);
        $this->assertSame('Invalid business short code', $result['ResultDesc']);
    }

    public function test_query_status_returns_failure_when_token_fails(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([], 401),
        ]);

        $result = $this->service->queryStatus('checkout-001');

        $this->assertFalse($result['success']);
        $this->assertSame('Failed to get access token', $result['message']);
    }

    public function test_query_status_successful(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'test-token'], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query' => Http::response([
                'ResponseCode' => '0',
                'ResultDesc' => 'The service request has been processed successfully',
                'ResultCode' => 0,
            ], 200),
        ]);

        $result = $this->service->queryStatus('checkout-001');

        $this->assertSame('0', $result['ResponseCode']);
        $this->assertSame(0, $result['ResultCode']);
    }

    public function test_clean_stale_pending_transactions(): void
    {
        $fresh = new MpesaTransaction;
        $fresh->merchant_request_id = 'fresh';
        $fresh->phone_number = '254712345678';
        $fresh->amount = 100;
        $fresh->status = 'pending';
        $fresh->created_at = now()->subMinutes(5);
        $fresh->save();

        $stale = new MpesaTransaction;
        $stale->merchant_request_id = 'stale';
        $stale->phone_number = '254712345679';
        $stale->amount = 200;
        $stale->status = 'pending';
        $stale->created_at = now()->subMinutes(120);
        $stale->save();

        $count = $this->service->cleanStalePendingTransactions(60);

        $this->assertSame(1, $count);

        $this->assertDatabaseHas('mpesa_transactions', [
            'id' => $stale->id,
            'status' => 'failed',
            'result_desc' => 'Transaction timed out - no callback received',
        ]);

        $this->assertDatabaseHas('mpesa_transactions', [
            'id' => $fresh->id,
            'status' => 'pending',
        ]);
    }

    public function test_clean_stale_returns_zero_when_none_stale(): void
    {
        $txn = new MpesaTransaction;
        $txn->merchant_request_id = 'active';
        $txn->phone_number = '254712345678';
        $txn->amount = 100;
        $txn->status = 'pending';
        $txn->created_at = now()->subMinutes(5);
        $txn->save();

        $count = $this->service->cleanStalePendingTransactions(60);

        $this->assertSame(0, $count);
    }
}
