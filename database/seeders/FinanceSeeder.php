<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Transaction;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $librarian = User::where('email', 'librarian@ollmchs.ac.ke')->first();
        $financeOfficer = User::where('email', 'finance@ollmchs.ac.ke')->first();
        $student = User::where('email', 'student@ollmchs.ac.ke')->first();

        if (!$librarian) return;

        $recorder = $financeOfficer ?? $librarian;

        if (!$student) return;

        // Create a pending fine for demo
        $pendingFine = Fine::create([
            'borrow_record_id' => \App\Modules\Circulation\Models\BorrowRecord::first()?->id ?? 1,
            'user_id' => $student->id,
            'reason' => 'overdue',
            'amount' => 350,
            'status' => 'pending',
            'assessed_at' => now()->subDays(3),
            'assessed_by' => $librarian->id,
        ]);

        // Create a paid transaction + receipt + invoice for demo
        $paidFine = Fine::create([
            'borrow_record_id' => \App\Modules\Circulation\Models\BorrowRecord::skip(1)->first()?->id ?? 1,
            'user_id' => $student->id,
            'reason' => 'overdue',
            'amount' => 500,
            'status' => 'paid',
            'assessed_at' => now()->subDays(10),
            'paid_at' => now()->subDays(8),
            'assessed_by' => $librarian->id,
        ]);

        $txn = Transaction::create([
            'user_id' => $student->id,
            'fine_id' => $paidFine->id,
            'transaction_number' => Transaction::generateNumber(),
            'type' => 'fine_payment',
            'payment_method' => 'cash',
            'amount' => $paidFine->amount,
            'currency' => 'KES',
            'description' => 'Payment for overdue fine',
            'status' => 'completed',
            'paid_at' => now()->subDays(8),
            'recorded_by' => $recorder->id,
        ]);

        \App\Modules\Finance\Models\Receipt::create([
            'receipt_number' => \App\Modules\Finance\Models\Receipt::generateNumber(),
            'transaction_id' => $txn->id,
            'user_id' => $student->id,
            'amount' => $paidFine->amount,
            'currency' => 'KES',
            'payment_method' => 'cash',
            'issued_at' => $txn->paid_at,
            'issued_by' => $recorder->id,
        ]);

        Invoice::create([
            'invoice_number' => Invoice::generateNumber(),
            'user_id' => $student->id,
            'transaction_id' => $txn->id,
            'amount' => $paidFine->amount,
            'currency' => 'KES',
            'status' => 'paid',
            'description' => 'Overdue fine invoice',
            'type' => 'fine',
            'issued_at' => $txn->paid_at,
            'due_at' => $txn->paid_at,
            'paid_at' => $txn->paid_at,
            'issued_by' => $recorder->id,
        ]);
    }
}
