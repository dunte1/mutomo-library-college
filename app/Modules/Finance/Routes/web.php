<?php

use App\Modules\Finance\Livewire\AnalyticsDashboard;
use App\Modules\Finance\Livewire\CollectPayments;
use App\Modules\Finance\Livewire\FinanceDashboard;
use App\Modules\Finance\Livewire\FineManagement;
use App\Modules\Finance\Livewire\InvoiceList;
use App\Modules\Finance\Livewire\ReceiptView;
use App\Modules\Finance\Livewire\RefundManagement;
use App\Modules\Finance\Livewire\ReportViewer;
use App\Modules\Finance\Livewire\TransactionList;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Receipt;
use App\Modules\Finance\Services\BillingService;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', FinanceDashboard::class)->name('index')->middleware('permission:view-financial-reports');
    Route::get('/transactions', TransactionList::class)->name('transactions')->middleware('permission:view-transactions');
    Route::get('/fines', FineManagement::class)->name('fines')->middleware('permission:manage-fines');
    Route::get('/reports', ReportViewer::class)->name('reports')->middleware('permission:generate-reports');
    Route::get('/analytics', AnalyticsDashboard::class)->name('analytics')->middleware('permission:view-analytics');
    Route::get('/receipts', ReceiptView::class)->name('receipts')->middleware('permission:view-transactions');
    Route::get('/receipt/{id}', ReceiptView::class)->name('receipt')->middleware('permission:view-transactions');
    Route::get('/collect-payments', CollectPayments::class)->name('collect-payments')->middleware('permission:collect-payments');
    Route::get('/invoices', InvoiceList::class)->name('invoices')->middleware('permission:generate-invoices');
    Route::get('/refunds', RefundManagement::class)->name('refunds')->middleware('permission:process-refunds');

    Route::get('/invoice/{invoice}/download', function (Invoice $invoice) {
        return app(BillingService::class)->downloadInvoice($invoice);
    })->name('invoice.download')->middleware('permission:view-transactions');

    Route::get('/invoice/{invoice}/email', function (Invoice $invoice) {
        app(BillingService::class)->emailInvoice($invoice);
        return back()->with('success', 'Invoice emailed successfully.');
    })->name('invoice.email')->middleware('permission:generate-invoices');

    Route::get('/receipt/{receipt}/download', function (Receipt $receipt) {
        return app(BillingService::class)->downloadReceipt($receipt);
    })->name('receipt.download')->middleware('permission:view-transactions');

    Route::get('/receipt/{receipt}/email', function (Receipt $receipt) {
        app(BillingService::class)->emailReceipt($receipt);
        return back()->with('success', 'Receipt emailed successfully.');
    })->name('receipt.email')->middleware('permission:generate-receipts');
});
