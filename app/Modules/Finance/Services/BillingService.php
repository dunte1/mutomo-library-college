<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Receipt;
use App\Modules\Settings\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class BillingService
{
    public function generateInvoicePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $settingsService = app(SettingsService::class);
        $branding = $settingsService->getBrandingSettings();
        $display = $settingsService->getDisplaySettings();

        $pdf = Pdf::loadView('finance::pdf.invoice', [
            'invoice' => $invoice,
            'branding' => $branding,
            'display' => $display,
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function generateReceiptPdf(Receipt $receipt): \Barryvdh\DomPDF\PDF
    {
        $settingsService = app(SettingsService::class);
        $branding = $settingsService->getBrandingSettings();
        $display = $settingsService->getDisplaySettings();

        $pdf = Pdf::loadView('finance::pdf.receipt', [
            'receipt' => $receipt,
            'transaction' => $receipt->transaction,
            'branding' => $branding,
            'display' => $display,
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function downloadInvoice(Invoice $invoice): Response
    {
        $pdf = $this->generateInvoicePdf($invoice);
        $filename = "invoice-{$invoice->invoice_number}.pdf";

        return $pdf->download($filename);
    }

    public function downloadReceipt(Receipt $receipt): Response
    {
        $pdf = $this->generateReceiptPdf($receipt);
        $filename = "receipt-{$receipt->receipt_number}.pdf";

        return $pdf->download($filename);
    }

    public function emailInvoice(Invoice $invoice): void
    {
        $pdf = $this->generateInvoicePdf($invoice);
        $pdfContent = $pdf->output();

        $user = $invoice->user;
        if ($user && $user->email) {
            Mail::send([], [], function ($message) use ($user, $invoice, $pdfContent) {
                $message->to($user->email)
                    ->subject("Invoice {$invoice->invoice_number}")
                    ->text("Please find attached invoice {$invoice->invoice_number} for KES {$invoice->amount}")
                    ->attachData($pdfContent, "invoice-{$invoice->invoice_number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });
        }
    }

    public function emailReceipt(Receipt $receipt): void
    {
        $pdf = $this->generateReceiptPdf($receipt);
        $pdfContent = $pdf->output();

        $user = $receipt->user;
        if ($user && $user->email) {
            Mail::send([], [], function ($message) use ($user, $receipt, $pdfContent) {
                $message->to($user->email)
                    ->subject("Receipt {$receipt->receipt_number}")
                    ->text("Please find attached receipt {$receipt->receipt_number} for KES {$receipt->amount}")
                    ->attachData($pdfContent, "receipt-{$receipt->receipt_number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });
        }
    }
}
