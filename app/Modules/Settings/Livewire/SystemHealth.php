<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SystemHealthService;
use Livewire\Component;

class SystemHealth extends Component
{
    public array $checks = [];

    public array $overall = [];

    public array $optimizationLogs = [];

    public string $activeTab = 'health';

    public function mount(SystemHealthService $healthService): void
    {
        $this->runChecks($healthService);
    }

    public function runChecks(SystemHealthService $healthService): void
    {
        $this->checks = $healthService->runAllChecks();
        $this->overall = $healthService->getOverallStatus($this->checks);
    }

    public function refreshHealth(): void
    {
        $this->runChecks(app(SystemHealthService::class));
        $this->dispatch('notify', type: 'success', message: 'Health check completed.');
    }

    public function clearCache(): void
    {
        $this->authorize('manage-system-optimization');
        $service = app(SystemHealthService::class);
        $result = $service->clearCache();
        $this->optimizationLogs = $result['logs'];
        $this->activeTab = 'optimization';
        $this->dispatch('notify', type: 'success', message: 'Cache cleared successfully.');
    }

    public function rebuildCache(): void
    {
        $this->authorize('manage-system-optimization');
        try {
            $service = app(SystemHealthService::class);
            $result = $service->rebuildCache();
            $this->optimizationLogs = $result['logs'];
            $this->activeTab = 'optimization';
            $this->dispatch('notify', type: 'success', message: 'Cache rebuilt successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to rebuild cache: '.$e->getMessage());
        }
    }

    public function optimizeSystem(): void
    {
        $this->authorize('manage-system-optimization');
        try {
            $service = app(SystemHealthService::class);
            $result = $service->optimizeSystem();
            $this->optimizationLogs = $result['logs'];
            $this->activeTab = 'optimization';
            $this->dispatch('notify', type: 'success', message: 'System optimization completed.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to optimize system: '.$e->getMessage());
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('settings::livewire.system-health', [
            'checks' => $this->checks,
            'overall' => $this->overall,
        ]);
    }
}
