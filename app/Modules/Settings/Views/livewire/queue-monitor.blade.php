<div>
    <div class="page-header">
        <h1 class="page-title">Queue Monitor</h1>
        <p class="page-subtitle">Monitor job queues and failed jobs</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stat-card">
            <p class="stat-label">Queue Connection</p>
            <p class="stat-value text-lg">{{ $queueStats['queue_connection'] }}</p>
        </div>
        <div class="stat-card bg-blue-50 dark:bg-blue-900/20">
            <p class="stat-label text-blue-600 dark:text-blue-400">Pending Jobs</p>
            <p class="stat-value text-blue-600 dark:text-blue-400">{{ $queueStats['jobs_count'] }}</p>
        </div>
        <div class="stat-card bg-red-50 dark:bg-red-900/20">
            <p class="stat-label text-red-600 dark:text-red-400">Failed Jobs</p>
            <p class="stat-value text-red-600 dark:text-red-400">{{ $queueStats['failed_jobs_count'] }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Failed Jobs</h3>
                <div class="flex gap-2">
                    <button wire:click="refreshStats" class="btn-sm btn-secondary">Refresh</button>
                    @if($queueStats['failed_jobs_count'] > 0)
                    <button wire:click="retryAll" wire:confirm="Retry all failed jobs?" class="btn-sm btn-primary">Retry All</button>
                    <button wire:click="flushFailed" wire:confirm="Permanently delete all failed job records?" class="btn-sm btn-danger">Flush</button>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto table-mobile-cards">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Connection</th>
                            <th>Queue</th>
                            <th>Failed At</th>
                            <th>Exception</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failedJobs as $job)
                        <tr>
                            <td class="font-mono text-xs">{{ $job->id }}</td>
                            <td class="text-sm">{{ $job->connection }}</td>
                            <td class="text-sm">{{ $job->queue }}</td>
                            <td class="text-sm text-surface-500">{{ \Carbon\Carbon::parse($job->failed_at)->format('M d, Y H:i') }}</td>
                            <td class="text-xs text-red-600 max-w-xs truncate">{{ explode("\n", $job->exception ?? '')[0] ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-surface-400">No failed jobs.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
