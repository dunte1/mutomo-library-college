<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Finance\Services\AnalyticsService;
use Livewire\Component;

class AnalyticsDashboard extends Component
{
    public array $analytics = [];

    public function mount()
    {
        $this->analytics = app(AnalyticsService::class)->getDashboardAnalytics();
    }

    public function render()
    {
        return view('finance::livewire.analytics-dashboard')
            ->layout('layouts.app');
    }
}
