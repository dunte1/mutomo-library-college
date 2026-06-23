<?php

namespace App\Modules\Settings\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class SystemHealthService
{
    /**
     * Run all health checks and return results.
     */
    public function runAllChecks(): array
    {
        return [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMail(),
            'migrations' => $this->checkMigrations(),
            'permissions' => $this->checkPermissions(),
            'routes' => $this->checkRoutes(),
            'environment' => $this->checkEnvironment(),
            'failed_jobs' => $this->checkFailedJobs(),
            'symlinks' => $this->checkSymlinks(),
            'missing_assets' => $this->checkMissingAssets(),
        ];
    }

    /**
     * Determine overall system health status.
     */
    public function getOverallStatus(array $checks): array
    {
        $healthy = 0;
        $warning = 0;
        $critical = 0;
        $recommendations = [];

        foreach ($checks as $name => $check) {
            match ($check['status']) {
                'healthy' => $healthy++,
                'warning' => $warning++,
                'critical' => $critical++,
                default => null,
            };

            if (! empty($check['recommendations'])) {
                foreach ($check['recommendations'] as $rec) {
                    $recommendations[] = [
                        'check' => $name,
                        'message' => $rec,
                        'severity' => $check['status'],
                    ];
                }
            }
        }

        if ($critical > 0) {
            $overall = 'critical';
        } elseif ($warning > 0) {
            $overall = 'warning';
        } else {
            $overall = 'healthy';
        }

        return [
            'overall' => $overall,
            'healthy' => $healthy,
            'warning' => $warning,
            'critical' => $critical,
            'total' => count($checks),
            'recommendations' => $recommendations,
        ];
    }

    // ─── Individual Checks ─────────────────────────────────────────

    public function checkApp(): array
    {
        $version = app()->version();
        $env = app()->environment();
        $debug = config('app.debug');
        $url = config('app.url');

        $issues = [];
        $recommendations = [];

        if ($debug && $env === 'production') {
            $issues[] = 'APP_DEBUG is enabled in production';
            $recommendations[] = 'Set APP_DEBUG=false in production environment';
        }

        if (empty($url)) {
            $issues[] = 'APP_URL is not configured';
            $recommendations[] = 'Set APP_URL in your .env file';
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'warning',
            'label' => 'Application',
            'details' => [
                'Version' => $version,
                'Environment' => $env,
                'Debug Mode' => $debug ? 'Enabled ⚠️' : 'Disabled ✅',
                'URL' => $url ?: 'Not set',
            ],
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    public function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $name = DB::connection()->getDatabaseName();
            $driver = DB::connection()->getDriverName();

            // Check if migrations table exists and get pending count
            $hasMigrationsTable = Schema::hasTable('migrations');
            $pendingMigrations = 0;

            if ($hasMigrationsTable) {
                $ran = DB::table('migrations')->pluck('migration');
                $files = File::glob(database_path('migrations/*.php'));
                $pendingMigrations = count($files) - $ran->count();
            }

            return [
                'status' => $pendingMigrations > 0 ? 'warning' : 'healthy',
                'label' => 'Database',
                'details' => [
                    'Driver' => $driver,
                    'Database' => $name,
                    'Connection' => 'OK ✅',
                    'Pending Migrations' => $pendingMigrations,
                ],
                'issues' => $pendingMigrations > 0 ? ["{$pendingMigrations} pending migration(s)"] : [],
                'recommendations' => $pendingMigrations > 0 ? ['Run php artisan migrate to apply pending migrations'] : [],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'critical',
                'label' => 'Database',
                'details' => ['Connection' => 'Failed ❌'],
                'issues' => [$e->getMessage()],
                'recommendations' => ['Check database configuration in .env file and ensure the database server is running'],
            ];
        }
    }

    public function checkCache(): array
    {
        try {
            $key = '_health_'.time();
            Cache::put($key, true, 1);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            if ($retrieved !== true) {
                return [
                    'status' => 'critical',
                    'label' => 'Cache',
                    'details' => ['Driver' => config('cache.default'), 'Status' => 'Write/Read mismatch ❌'],
                    'issues' => ['Cache write/read mismatch detected'],
                    'recommendations' => ['Check cache configuration in config/cache.php'],
                ];
            }

            return [
                'status' => 'healthy',
                'label' => 'Cache',
                'details' => [
                    'Driver' => config('cache.default'),
                    'Status' => 'Working ✅',
                ],
                'issues' => [],
                'recommendations' => [],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'critical',
                'label' => 'Cache',
                'details' => ['Driver' => config('cache.default'), 'Status' => 'Failed ❌'],
                'issues' => [$e->getMessage()],
                'recommendations' => ['Check cache configuration and ensure the selected cache driver is available'],
            ];
        }
    }

    public function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $disk->put('_health.txt', 'ok');
            $content = $disk->get('_health.txt');
            $disk->delete('_health.txt');

            if ($content !== 'ok') {
                return [
                    'status' => 'critical',
                    'label' => 'Storage',
                    'details' => ['Status' => 'Write/Read mismatch ❌'],
                    'issues' => ['Storage write/read mismatch'],
                    'recommendations' => ['Check storage permissions in storage/app/'],
                ];
            }

            $free = disk_free_space(storage_path());
            $total = disk_total_space(storage_path());
            $usagePercent = $total > 0 ? round((1 - $free / $total) * 100, 2) : 0;

            $status = $usagePercent > 90 ? 'critical' : ($usagePercent > 75 ? 'warning' : 'healthy');
            $issues = [];
            $recommendations = [];

            if ($usagePercent > 90) {
                $issues[] = "Disk usage at {$usagePercent}%";
                $recommendations[] = 'Free up disk space immediately - usage exceeds 90%';
            } elseif ($usagePercent > 75) {
                $recommendations[] = 'Consider freeing up disk space - usage exceeds 75%';
            }

            $freeGB = round($free / 1073741824, 2);

            return [
                'status' => $status,
                'label' => 'Storage',
                'details' => [
                    'Disk Usage' => "{$usagePercent}%",
                    'Free Space' => "{$freeGB} GB",
                    'Status' => 'Working ✅',
                ],
                'issues' => $issues,
                'recommendations' => $recommendations,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'critical',
                'label' => 'Storage',
                'details' => ['Status' => 'Failed ❌'],
                'issues' => [$e->getMessage()],
                'recommendations' => ['Check storage directory permissions'],
            ];
        }
    }

    public function checkQueue(): array
    {
        try {
            $driver = config('queue.default');
            $issues = [];
            $recommendations = [];

            if ($driver === 'sync') {
                $recommendations[] = 'Queue driver is set to "sync". Consider using "database" for better performance';
            }

            // Check if the jobs table exists if using database driver
            if ($driver === 'database' && ! Schema::hasTable(config('queue.connections.database.table', 'jobs'))) {
                $issues[] = 'Jobs table not found';
                $recommendations[] = 'Run php artisan queue:table and php artisan migrate';
            }

            return [
                'status' => empty($issues) ? 'healthy' : 'critical',
                'label' => 'Queue',
                'details' => [
                    'Driver' => $driver,
                    'Status' => empty($issues) ? 'Configured ✅' : 'Issues ❌',
                ],
                'issues' => $issues,
                'recommendations' => $recommendations,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'critical',
                'label' => 'Queue',
                'details' => ['Status' => 'Failed ❌'],
                'issues' => [$e->getMessage()],
                'recommendations' => ['Check queue configuration'],
            ];
        }
    }

    public function checkMail(): array
    {
        $driver = config('mail.default');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $issues = [];
        $recommendations = [];

        if ($driver === 'log') {
            $recommendations[] = 'Mail driver is set to "log". Emails will be logged but not sent';
        }

        if (! $fromAddress || $fromAddress === 'hello@example.com') {
            $issues[] = 'MAIL_FROM_ADDRESS not configured';
            $recommendations[] = 'Set MAIL_FROM_ADDRESS in your .env file';
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'warning',
            'label' => 'Mail',
            'details' => [
                'Driver' => $driver,
                'From Address' => $fromAddress ?: 'Not set',
                'From Name' => $fromName ?: 'Not set',
            ],
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    public function checkMigrations(): array
    {
        try {
            if (! Schema::hasTable('migrations')) {
                return [
                    'status' => 'critical',
                    'label' => 'Migrations',
                    'details' => ['Status' => 'Migrations table not found ❌'],
                    'issues' => ['Migrations table does not exist'],
                    'recommendations' => ['Run php artisan migrate:install then php artisan migrate'],
                ];
            }

            $ran = DB::table('migrations')->pluck('migration');
            $files = File::glob(database_path('migrations/*.php'));
            $pending = [];

            foreach ($files as $file) {
                $migrationName = basename($file, '.php');
                if (! $ran->contains($migrationName)) {
                    $pending[] = $migrationName;
                }
            }

            $issues = $pending ? ["{$pending} pending migration(s)"] : [];
            $recommendations = $pending ? ['Run php artisan migrate to apply pending migrations'] : [];

            return [
                'status' => empty($pending) ? 'healthy' : 'warning',
                'label' => 'Migrations',
                'details' => [
                    'Total' => count($files),
                    'Applied' => $ran->count(),
                    'Pending' => count($pending),
                ],
                'issues' => $issues,
                'recommendations' => $recommendations,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'critical',
                'label' => 'Migrations',
                'details' => ['Status' => 'Failed ❌'],
                'issues' => [$e->getMessage()],
                'recommendations' => ['Check database connection'],
            ];
        }
    }

    public function checkPermissions(): array
    {
        try {
            $expectedPermissions = [
                'view-dashboard', 'view-books', 'create-books', 'view-members', 'create-members',
                'manage-announcements', 'manage-events', 'send-messages', 'view-messages',
                'manage-settings', 'manage-roles', 'view-audit-logs', 'manage-backups',
                'view-system-logs', 'manage-maintenance',
                'view-library-cards', 'manage-library-cards',
                'view-system-health', 'manage-system-optimization',
            ];

            $existing = Permission::pluck('name')->toArray();
            $missing = array_diff($expectedPermissions, $existing);

            $issues = $missing ? ['Missing permissions: '.implode(', ', $missing)] : [];
            $recommendations = $missing ? ['Run php artisan db:seed --class=RolesAndPermissionsSeeder'] : [];

            return [
                'status' => empty($missing) ? 'healthy' : 'warning',
                'label' => 'Permissions',
                'details' => [
                    'Expected' => count($expectedPermissions),
                    'Existing' => count($existing),
                    'Missing' => count($missing),
                ],
                'issues' => $issues,
                'recommendations' => $recommendations,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'warning',
                'label' => 'Permissions',
                'details' => ['Status' => 'Could not verify ❌'],
                'issues' => [$e->getMessage()],
                'recommendations' => ['Ensure Spatie permissions package is installed and migrations have run'],
            ];
        }
    }

    public function checkRoutes(): array
    {
        try {
            $routes = collect(Route::getRoutes())->map(fn ($r) => $r->getName())->filter()->values();

            $expected = [
                'dashboard', 'profile', 'settings.index',
                'catalog.books.index', 'communication.messages.index', 'members.index',
                'circulation.index', 'finance.index', 'digital-library.index',
                'notifications.index',
            ];

            $missing = [];
            foreach ($expected as $route) {
                if (! $routes->contains($route)) {
                    $missing[] = $route;
                }
            }

            $issues = $missing ? ['Missing routes: '.implode(', ', $missing)] : [];
            $recommendations = $missing ? ['Check route registrations in module service providers'] : [];

            return [
                'status' => empty($missing) ? 'healthy' : 'warning',
                'label' => 'Routes',
                'details' => [
                    'Total Registered' => $routes->count(),
                    'Missing Expected' => count($missing),
                ],
                'issues' => $issues,
                'recommendations' => $recommendations,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'warning',
                'label' => 'Routes',
                'details' => ['Status' => 'Could not verify ❌'],
                'issues' => [$e->getMessage()],
                'recommendations' => ['Check route caching'],
            ];
        }
    }

    public function checkEnvironment(): array
    {
        $envFile = base_path('.env');
        $issues = [];
        $recommendations = [];

        if (! File::exists($envFile)) {
            return [
                'status' => 'critical',
                'label' => 'Environment',
                'details' => ['.env' => 'Not found ❌'],
                'issues' => ['.env file is missing'],
                'recommendations' => ['Copy .env.example to .env and configure your settings'],
            ];
        }

        $env = app()->environment();
        $key = config('app.key');
        $debug = config('app.debug');

        if (! $key || $key === 'base64:...') {
            $issues[] = 'Application key not set';
            $recommendations[] = 'Run php artisan key:generate';
        }

        if ($debug && $env === 'production') {
            $issues[] = 'Debug mode enabled in production';
            $recommendations[] = 'Set APP_DEBUG=false in production';
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'warning',
            'label' => 'Environment',
            'details' => [
                'Environment' => $env,
                'App Key' => $key ? 'Set ✅' : 'Not set ❌',
                'Debug Mode' => $debug ? 'Enabled ⚠️' : 'Disabled ✅',
                '.env File' => 'Found ✅',
            ],
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    public function checkFailedJobs(): array
    {
        try {
            if (! Schema::hasTable('failed_jobs')) {
                return [
                    'status' => 'healthy',
                    'label' => 'Failed Jobs',
                    'details' => ['Table' => 'Not used', 'Failed Jobs' => 0],
                    'issues' => [],
                    'recommendations' => [],
                ];
            }

            $count = DB::table('failed_jobs')->count();

            $issues = $count > 0 ? ["{$count} failed job(s) in the queue"] : [];
            $recommendations = $count > 0 ? ['Check failed jobs with php artisan queue:failed and retry with php artisan queue:retry all'] : [];

            return [
                'status' => $count > 0 ? 'warning' : 'healthy',
                'label' => 'Failed Jobs',
                'details' => ['Failed Jobs' => $count],
                'issues' => $issues,
                'recommendations' => $recommendations,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'healthy',
                'label' => 'Failed Jobs',
                'details' => ['Status' => 'Not available'],
                'issues' => [],
                'recommendations' => [],
            ];
        }
    }

    public function checkSymlinks(): array
    {
        $publicStorage = public_path('storage');
        $issues = [];
        $recommendations = [];

        if (! File::exists($publicStorage)) {
            $issues[] = 'public/storage symlink does not exist';
            $recommendations[] = 'Run php artisan storage:link to create the symlink';
        } elseif (! is_link($publicStorage)) {
            $issues[] = 'public/storage is a directory, not a symlink';
            $recommendations[] = 'Remove the public/storage directory and run php artisan storage:link';
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'warning',
            'label' => 'Storage Links',
            'details' => [
                'public/storage' => File::exists($publicStorage) ? 'Exists ✅' : 'Missing ❌',
                'Is Symlink' => $issues ? 'No ❌' : 'Yes ✅',
            ],
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    public function checkMissingAssets(): array
    {
        $assets = [
            'public/manifest.json' => 'PWA manifest',
            'public/sw.js' => 'Service Worker',
            'public/robots.txt' => 'Robots file',
        ];

        $missing = [];
        foreach ($assets as $path => $name) {
            if (! File::exists(base_path($path))) {
                $missing[] = $name;
            }
        }

        $issues = $missing ? ['Missing assets: '.implode(', ', $missing)] : [];
        $recommendations = $missing ? ['Ensure frontend build process has been run'] : [];

        return [
            'status' => empty($missing) ? 'healthy' : 'warning',
            'label' => 'Assets',
            'details' => [
                'Checked' => count($assets),
                'Missing' => count($missing),
            ],
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    // ─── Optimization Commands ──────────────────────────────────────

    public function clearCache(): array
    {
        $logs = [];

        try {
            Artisan::call('route:clear');
            $logs[] = ['command' => 'route:clear', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'route:clear', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('view:clear');
            $logs[] = ['command' => 'view:clear', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'view:clear', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('config:clear');
            $logs[] = ['command' => 'config:clear', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'config:clear', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('cache:clear');
            $logs[] = ['command' => 'cache:clear', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'cache:clear', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        $this->auditAction('Cleared all caches', $logs);

        return ['success' => true, 'logs' => $logs];
    }

    public function rebuildCache(): array
    {
        $logs = [];

        // Clear existing caches first, then rebuild
        try {
            Artisan::call('config:clear');
            $logs[] = ['command' => 'config:clear', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'config:clear', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('config:cache');
            $logs[] = ['command' => 'config:cache', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'config:cache', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('route:clear');
            $logs[] = ['command' => 'route:clear', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'route:clear', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('route:cache');
            $logs[] = ['command' => 'route:cache', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'route:cache', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('view:clear');
            $logs[] = ['command' => 'view:clear', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'view:clear', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        try {
            Artisan::call('view:cache');
            $logs[] = ['command' => 'view:cache', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'view:cache', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        // Clear cached settings so fresh ones load
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'cache:flush-settings', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        $this->auditAction('Rebuilt all caches', $logs);

        return ['success' => true, 'logs' => $logs];
    }

    public function optimizeSystem(): array
    {
        $logs = [];

        try {
            Artisan::call('optimize');
            $logs[] = ['command' => 'optimize', 'status' => 'success', 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            $logs[] = ['command' => 'optimize', 'status' => 'failure', 'output' => $e->getMessage()];
        }

        // Check app health after optimization
        $health = $this->runAllChecks();
        $overall = $this->getOverallStatus($health);

        $logs[] = [
            'command' => 'system-optimization-check',
            'status' => $overall['overall'] !== 'critical' ? 'success' : 'failure',
            'output' => "System status: {$overall['overall']} ({$overall['healthy']}/{$overall['total']} checks passed)",
        ];

        $this->auditAction('Optimized system', $logs);

        return ['success' => true, 'logs' => $logs];
    }

    protected function auditAction(string $action, array $logs): void
    {
        try {
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => $action,
                    'results' => collect($logs)->pluck('status', 'command')->toArray(),
                ])
                ->log("System optimization: {$action}");
        } catch (\Throwable $e) {
            // Silently fail — don't break the optimization for an audit log
        }
    }
}
