<?php

namespace App\Modules\Finance\Services;

use App\Models\User;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Receipt;
use App\Modules\Finance\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public function recordFinePayment(Fine $fine, string $paymentMethod = 'cash', ?string $reference = null): Transaction
    {
        $borrowRecord = $fine->borrowRecord;

        if (!$borrowRecord || !$borrowRecord->user) {
            throw new \RuntimeException('Fine has no associated borrow record or user.');
        }

        $user = $borrowRecord->user;

        return DB::transaction(function () use ($fine, $user, $paymentMethod, $reference) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'fine_id' => $fine->id,
                'transaction_number' => Transaction::generateNumber(),
                'type' => match ($fine->type) {
                    'lost_book' => 'lost_book_fine',
                    'damage' => 'damage_fine',
                    default => 'fine_payment',
                },
                'payment_method' => $paymentMethod,
                'amount' => $fine->amount,
                'currency' => 'KES',
                'reference' => $reference,
                'description' => "Payment for {$fine->type} fine (KES {$fine->amount})",
                'status' => 'completed',
                'paid_at' => now(),
                'recorded_by' => auth()->id(),
            ]);

            $fine->update(['status' => 'paid']);

            $this->generateReceipt($transaction);

            return $transaction;
        });
    }

    public function generateInvoice(User $user, float $amount, string $type = 'fine', ?string $description = null): Invoice
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateNumber(),
            'user_id' => $user->id,
            'amount' => $amount,
            'currency' => 'KES',
            'status' => 'pending',
            'description' => $description,
            'type' => $type,
            'issued_at' => now(),
            'due_at' => now()->addDays(14),
            'issued_by' => auth()->id(),
        ]);

        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->log("Generated invoice {$invoice->invoice_number} for KES {$amount}");

        return $invoice;
    }

    public function generateReceipt(Transaction $transaction): Receipt
    {
        $receipt = Receipt::create([
            'receipt_number' => Receipt::generateNumber(),
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'payment_method' => $transaction->payment_method ?? 'cash',
            'issued_at' => now(),
            'issued_by' => auth()->id(),
        ]);

        if ($transaction->invoice) {
            $transaction->invoice->markAsPaid();
        }

        return $receipt;
    }

    public function getDashboardStats(): array
    {
        return [
            'total_fines_collected' => Transaction::completed()->ofType('fine_payment')->sum('amount'),
            'total_lost_fines' => Transaction::completed()->ofType('lost_book_fine')->sum('amount'),
            'total_damage_fines' => Transaction::completed()->ofType('damage_fine')->sum('amount'),
            'today_collections' => Transaction::completed()->whereDate('paid_at', today())->sum('amount'),
            'month_collections' => Transaction::completed()->whereMonth('paid_at', now()->month)->sum('amount'),
            'pending_fines' => Fine::pending()->sum('amount'),
            'pending_fine_count' => Fine::pending()->count(),
            'transaction_count' => Transaction::completed()->count(),
            'recent_transactions' => Transaction::with('user')->latest()->take(5)->get(),
        ];
    }
}
