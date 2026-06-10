<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Finance\Services\FinanceService;
use App\Modules\Finance\Services\AnalyticsService;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Livewire\Component;

class FinanceDashboard extends Component
{
    public array $stats = [];
    public array $analytics = [];
    public array $subscriptionRevenue = [];

    public function mount()
    {
        $this->stats = app(FinanceService::class)->getDashboardStats();
        $this->analytics = app(AnalyticsService::class)->getDashboardAnalytics();
        $this->subscriptionRevenue = app(SubscriptionService::class)->getRevenueStats();
    }

    public function render()
    {
        return view('finance::livewire.finance-dashboard')
            ->layout('layouts.app');
    }
}
