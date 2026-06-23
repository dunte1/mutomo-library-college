<?php

namespace App\Modules\Finance\Services;

use App\Mail\PaymentConfirmation;
use App\Mail\SubscriptionActivation;
use App\Modules\Finance\Models\MpesaTransaction;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MpesaService
{
    protected string $consumerKey;

    protected string $consumerSecret;

    protected string $passKey;

    protected string $shortCode;

    protected string $environment;

    public function __construct()
    {
        $this->consumerKey = config('mpesa.consumer_key', 'test_key');
        $this->consumerSecret = config('mpesa.consumer_secret', 'test_secret');
        $this->passKey = config('mpesa.pass_key', 'test_pass');
        $this->shortCode = config('mpesa.short_code', '174379');
        $this->environment = config('mpesa.env', 'sandbox');
    }

    public function stkPush(float $amount, string $phone, string $reference = 'Library Payment', ?int $userId = null): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero'];
        }

        $formattedPhone = $this->formatPhone($phone);
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortCode.$this->passKey.$timestamp);

        $callbackUrl = route('api.mpesa.callback', [], false);

        $payload = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) round($amount),
            'PartyA' => $formattedPhone,
            'PartyB' => $this->shortCode,
            'PhoneNumber' => $formattedPhone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $reference,
            'TransactionDesc' => 'Library Payment',
        ];

        $token = $this->getAccessToken();

        if (! $token) {
            $mpesaTxn = MpesaTransaction::create([
                'phone_number' => $phone,
                'amount' => $amount,
                'status' => 'failed',
                'result_desc' => 'Failed to get access token',
            ]);

            return ['success' => false, 'message' => 'M-Pesa service unavailable'];
        }

        $response = Http::withToken($token)
            ->post("https://{$this->getBaseUrl()}/mpesa/stkpush/v1/processrequest", $payload);

        $result = $response->json();

        $mpesaTxn = MpesaTransaction::create([
            'merchant_request_id' => $result['MerchantRequestID'] ?? null,
            'checkout_request_id' => $result['CheckoutRequestID'] ?? null,
            'phone_number' => $phone,
            'amount' => $amount,
            'user_id' => $userId,
            'status' => ($result['ResponseCode'] ?? '1') === '0' ? 'pending' : 'failed',
            'result_desc' => $result['ResponseDescription'] ?? 'Unknown',
            'callback_data' => $result,
        ]);

        if (($result['ResponseCode'] ?? '1') === '0') {
            return [
                'success' => true,
                'message' => 'STK push sent. Check phone to complete payment.',
                'checkout_request_id' => $result['CheckoutRequestID'],
                'mpesa_transaction_id' => $mpesaTxn->id,
            ];
        }

        return ['success' => false, 'message' => $result['ResponseDescription'] ?? 'M-Pesa request failed'];
    }

    public function processCallback(array $data): void
    {
        $body = $data['Body'] ?? [];
        $stkCallback = $body['stkCallback'] ?? [];

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? $stkCallback['MerchantRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? 1;
        $resultDesc = $stkCallback['ResultDesc'] ?? '';

        $mpesaTxn = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)->first();

        if (! $mpesaTxn) {
            return;
        }

        if ($resultCode === 0) {
            $metadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
            $mpesaReceipt = '';
            $phone = '';
            $amount = 0;

            foreach ($metadata as $item) {
                match ($item['Name']) {
                    'MpesaReceiptNumber' => $mpesaReceipt = $item['Value'],
                    'PhoneNumber' => $phone = $item['Value'],
                    'Amount' => $amount = $item['Value'],
                    default => null,
                };
            }

            $pendingSubscription = $mpesaTxn->user_id
                ? Subscription::where('user_id', $mpesaTxn->user_id)
                    ->where('status', 'pending')
                    ->latest('id')
                    ->first()
                : null;

            $txnType = $pendingSubscription ? 'subscription_payment' : 'fine_payment';
            $txn = Transaction::create([
                'user_id' => $mpesaTxn->user_id,
                'subscription_id' => $pendingSubscription?->id,
                'transaction_number' => Transaction::generateNumber(),
                'type' => $txnType,
                'payment_method' => 'mpesa',
                'amount' => $amount,
                'currency' => 'KES',
                'reference' => $mpesaReceipt,
                'status' => 'completed',
                'paid_at' => now(),
                'recorded_by' => null,
            ]);

            if ($pendingSubscription) {
                $pendingSubscription->update(['status' => 'active']);

                // Generate invoice
                try {
                    $financeService = app(FinanceService::class);
                    $invoice = $financeService->generateInvoice(
                        $pendingSubscription->user,
                        $amount,
                        'subscription',
                        "M-Pesa subscription payment: {$pendingSubscription->plan->name}"
                    );
                    $invoice->update(['transaction_id' => $txn->id]);
                } catch (\Throwable $e) {
                    Log::warning("M-Pesa: Failed to generate invoice: {$e->getMessage()}");
                }

                // Generate receipt
                try {
                    $receipt = $financeService->generateReceipt($txn);
                } catch (\Throwable $e) {
                    Log::warning("M-Pesa: Failed to generate receipt: {$e->getMessage()}");
                }

                // Auto-issue library card
                try {
                    $member = Member::where('user_id', $mpesaTxn->user_id)->first();
                    if ($member && ! $member->libraryCard) {
                        app(LibraryCardService::class)->autoIssueCard($member);
                    }
                } catch (\Throwable $e) {
                    Log::warning("M-Pesa: Failed to auto-issue library card: {$e->getMessage()}");
                }

                // Send emails
                try {
                    $user = $pendingSubscription->user;
                    if ($user && $user->email) {
                        Mail::to($user->email)->queue(new SubscriptionActivation($pendingSubscription));
                        Mail::to($user->email)->queue(new PaymentConfirmation($txn, 'mpesa'));
                    }
                } catch (\Throwable $e) {
                    Log::warning("M-Pesa: Failed to send notification emails: {$e->getMessage()}");
                }

                // Email receipt
                try {
                    if ($txn->receipt) {
                        app(BillingService::class)->emailReceipt($txn->receipt);
                    }
                } catch (\Throwable $e) {
                    Log::warning("M-Pesa: Failed to email receipt: {$e->getMessage()}");
                }

                activity()
                    ->performedOn($pendingSubscription)
                    ->withProperties(['transaction_id' => $txn->id, 'amount' => $amount, 'mpesa_receipt' => $mpesaReceipt])
                    ->log("Subscription activated via M-Pesa payment: {$amount}");
            }

            $mpesaTxn->update([
                'mpesa_receipt' => $mpesaReceipt,
                'status' => 'success',
                'result_desc' => $resultDesc,
                'transaction_id' => $txn->id,
                'callback_data' => $data,
            ]);
        } else {
            $mpesaTxn->update([
                'status' => 'failed',
                'result_desc' => $resultDesc,
                'callback_data' => $data,
            ]);
        }
    }

    public function queryStatus(string $checkoutRequestId): array
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return ['success' => false, 'message' => 'Failed to get access token'];
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortCode.$this->passKey.$timestamp);

        try {
            $response = Http::withToken($token)
                ->post("https://{$this->getBaseUrl()}/mpesa/stkpushquery/v1/query", [
                    'BusinessShortCode' => $this->shortCode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'CheckoutRequestID' => $checkoutRequestId,
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('M-Pesa status query error: '.$e->getMessage());

            return ['success' => false, 'message' => 'Query failed'];
        }
    }

    public function cleanStalePendingTransactions(int $minutesOld = 60): int
    {
        $cutoff = now()->subMinutes($minutesOld);
        $stale = MpesaTransaction::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($stale as $txn) {
            $txn->update([
                'status' => 'failed',
                'result_desc' => 'Transaction timed out - no callback received',
            ]);

            if ($txn->user_id) {
                Subscription::where('user_id', $txn->user_id)
                    ->where('status', 'pending')
                    ->where('payment_method', 'mpesa')
                    ->each(fn ($s) => $s->update([
                        'status' => 'expired',
                        'cancellation_reason' => 'Payment timed out',
                    ]));
            }

            $count++;
        }

        return $count;
    }

    private function getAccessToken(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get("https://{$this->getBaseUrl()}/oauth/v1/generate?grant_type=client_credentials");

            return $response->json('access_token');
        } catch (\Exception $e) {
            Log::error('M-Pesa access token error: '.$e->getMessage());

            return null;
        }
    }

    private function getBaseUrl(): string
    {
        return $this->environment === 'production' ? 'api.safaricom.co.ke' : 'sandbox.safaricom.co.ke';
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 9) {
            $phone = '254'.$phone;
        }
        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        }
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}
