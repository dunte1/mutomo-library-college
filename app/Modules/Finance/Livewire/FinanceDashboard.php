<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Finance\Services\FinanceService;
use App\Modules\Finance\Services\AnalyticsService;
use Livewire\Component;

class FinanceDashboard extends Component
{
    public array $stats = [];
    public array $analytics = [];

    public function mount()
    {
        $this->stats = app(FinanceService::class)->getDashboardStats();
        $this->analytics = app(AnalyticsService::class)->getDashboardAnalytics();
    }

    public function render()
    {
        return view('finance::livewire.finance-dashboard')
            ->layout('layouts.app');
    }
}
