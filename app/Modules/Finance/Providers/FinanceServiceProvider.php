<?php

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Livewire\AnalyticsDashboard;
use App\Modules\Finance\Livewire\CollectPayments;
use App\Modules\Finance\Livewire\FinanceDashboard;
use App\Modules\Finance\Livewire\FineManagement;
use App\Modules\Finance\Livewire\InvoiceList;
use App\Modules\Finance\Livewire\ReceiptView;
use App\Modules\Finance\Livewire\RefundManagement;
use App\Modules\Finance\Livewire\ReportViewer;
use App\Modules\Finance\Livewire\TransactionList;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'finance');

        Livewire::component('finance-dashboard', FinanceDashboard::class);
        Livewire::component('fine-management', FineManagement::class);
        Livewire::component('transaction-list', TransactionList::class);
        Livewire::component('report-viewer', ReportViewer::class);
        Livewire::component('analytics-dashboard', AnalyticsDashboard::class);
        Livewire::component('receipt-view', ReceiptView::class);
        Livewire::component('collect-payments', CollectPayments::class);
        Livewire::component('invoice-list', InvoiceList::class);
        Livewire::component('refund-management', RefundManagement::class);
    }
}
