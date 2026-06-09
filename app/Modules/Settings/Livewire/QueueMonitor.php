<?php

namespace App\Modules\Settings\Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class QueueMonitor extends Component
{
    public array $queueStats = [];
    public array $failedJobs = [];
    public string $activeTab = 'overview';

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $this->queueStats = [
            'failed_jobs_count' => DB::table('failed_jobs')->count(),
            'jobs_count' => DB::table('jobs')->count(),
            'queue_connection' => config('queue.default'),
            'queue_driver' => config('queue.default'),
        ];

        $this->failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->take(20)
            ->get()
            ->toArray();
    }

    public function retryAll(): void
    {
        try {
            $this->authorize('manage-system-optimization');
            Artisan::call('queue:retry all');
            $this->refreshStats();
            $this->dispatch('notify', type: 'success', message: 'All failed jobs queued for retry.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to retry jobs: ' . $e->getMessage());
        }
    }

    public function flushFailed(): void
    {
        try {
            $this->authorize('manage-system-optimization');
            Artisan::call('queue:flush');
            $this->refreshStats();
            $this->dispatch('notify', type: 'success', message: 'Failed jobs table flushed.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to flush failed jobs: ' . $e->getMessage());
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('settings::livewire.queue-monitor', [
            'queueStats' => $this->queueStats,
            'failedJobs' => $this->failedJobs,
        ]);
    }
}
