<?php

namespace App\Modules\Settings\Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CacheManager extends Component
{
    public array $cacheStats = [];

    public string $activeTab = 'overview';

    public array $logs = [];

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $this->cacheStats = [
            'default_store' => config('cache.default'),
            'has_file_cache' => Cache::store('file')->has('__test'),
            'optimized' => file_exists(base_path('bootstrap/cache/config.php')),
            'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'events_cached' => file_exists(base_path('bootstrap/cache/events.php')),
        ];
    }

    public function clearAll(): void
    {
        try {
            $this->authorize('manage-cache');
            Artisan::call('optimize:clear');
            $this->logs = explode("\n", trim(Artisan::output()));
            $this->refreshStats();
            $this->dispatch('notify', type: 'success', message: 'All cache cleared successfully.');
        } catch (\Throwable $e) {
            $this->logs = [$e->getMessage()];
            $this->dispatch('notify', type: 'error', message: 'Failed to clear cache: '.$e->getMessage());
        }
    }

    public function optimizeSystem(): void
    {
        try {
            $this->authorize('manage-cache');
            Artisan::call('optimize');
            $this->logs = explode("\n", trim(Artisan::output()));
            $this->refreshStats();
            $this->dispatch('notify', type: 'success', message: 'System optimized successfully.');
        } catch (\Throwable $e) {
            $this->logs = [$e->getMessage()];
            $this->dispatch('notify', type: 'error', message: 'Failed to optimize: '.$e->getMessage());
        }
    }

    public function clearConfig(): void
    {
        try {
            $this->authorize('manage-cache');
            Artisan::call('config:clear');
            $this->logs = explode("\n", trim(Artisan::output()));
            $this->refreshStats();
            $this->dispatch('notify', type: 'success', message: 'Config cache cleared.');
        } catch (\Throwable $e) {
            $this->logs = [$e->getMessage()];
            $this->dispatch('notify', type: 'error', message: 'Failed to clear config cache: '.$e->getMessage());
        }
    }

    public function clearRoutes(): void
    {
        try {
            $this->authorize('manage-cache');
            Artisan::call('route:clear');
            $this->logs = explode("\n", trim(Artisan::output()));
            $this->refreshStats();
            $this->dispatch('notify', type: 'success', message: 'Route cache cleared.');
        } catch (\Throwable $e) {
            $this->logs = [$e->getMessage()];
            $this->dispatch('notify', type: 'error', message: 'Failed to clear route cache: '.$e->getMessage());
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('settings::livewire.cache-manager', [
            'cacheStats' => $this->cacheStats,
        ]);
    }
}
