<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        if (app()->environment('production')) {
            // In production, return minimal status only
            return response()->json([
                'status' => $healthy ? 'healthy' : 'degraded',
                'timestamp' => now()->toIso8601String(),
            ], $healthy ? 200 : 503);
        }

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function checkApp(): array
    {
        if (app()->environment('production')) {
            return ['status' => 'ok'];
        }

        return [
            'status' => 'ok',
            'version' => app()->version(),
            'env' => app()->environment(),
            'debug' => config('app.debug'),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $name = DB::connection()->getDatabaseName();

            return [
                'status' => 'ok',
                'connection' => config('database.default'),
                'database' => $name,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = '_health_'.time();
            Cache::put($key, true, 1);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            if ($retrieved !== true) {
                return ['status' => 'error', 'message' => 'Cache write/read mismatch'];
            }

            return [
                'status' => 'ok',
                'driver' => config('cache.default'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $disk->put('_health.txt', 'ok');
            $content = $disk->get('_health.txt');
            $disk->delete('_health.txt');

            if ($content !== 'ok') {
                return ['status' => 'error', 'message' => 'Storage write/read mismatch'];
            }

            $free = disk_free_space(storage_path());
            $total = disk_total_space(storage_path());
            $usagePercent = $total > 0 ? round((1 - $free / $total) * 100, 2) : 0;

            return [
                'status' => 'ok',
                'disk_usage_percent' => $usagePercent,
                'disk_free_bytes' => $free,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
