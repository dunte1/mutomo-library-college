<?php

namespace App\Mail;

use App\Modules\Finance\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public string $paymentMethod,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Confirmed - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        $user = $this->transaction->user;

        return new Content(
            markdown: 'emails.payment-confirmation',
            with: [
                'name' => $user->name,
                'transactionNumber' => $this->transaction->transaction_number,
                'amount' => number_format($this->transaction->amount, 2),
                'currency' => $this->transaction->currency ?? 'KES',
                'paymentMethod' => ucfirst($this->paymentMethod),
                'reference' => $this->transaction->reference,
                'description' => $this->transaction->description,
                'paidAt' => ($this->transaction->paid_at ?? $this->transaction->created_at)->format('d M Y H:i'),
                'libraryName' => config('app.name'),
                'libraryPhone' => config('app.library_phone', ''),
                'libraryEmail' => config('app.library_email', ''),
                'receiptUrl' => $this->transaction->receipt
                    ? route('finance.receipt', $this->transaction->id)
                    : null,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
