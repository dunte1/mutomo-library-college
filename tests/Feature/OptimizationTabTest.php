<?php

namespace Tests\Feature;

use App\Modules\Settings\Services\SystemHealthService;
use Tests\TestCase;

class OptimizationTabTest extends TestCase
{
    public function test_clear_cache_returns_results(): void
    {
        $service = app(SystemHealthService::class);
        $result = $service->clearCache();

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['logs']);

        // All commands should execute without exceptions
        foreach ($result['logs'] as $log) {
            $this->assertNotEmpty($log['command']);
        }

        // Verify expected commands are present (some may show 'failure' in test env due to missing tables)
        $commands = collect($result['logs'])->pluck('command')->toArray();
        $this->assertContains('route:clear', $commands);
        $this->assertContains('view:clear', $commands);
        $this->assertContains('config:clear', $commands);
        $this->assertContains('cache:clear', $commands);
    }

    public function test_rebuild_cache_returns_results(): void
    {
        $service = app(SystemHealthService::class);
        $result = $service->rebuildCache();

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['logs']);

        // Every log should have a command name
        foreach ($result['logs'] as $log) {
            $this->assertNotEmpty($log['command']);
        }

        // Verify expected commands are present
        $commands = collect($result['logs'])->pluck('command')->toArray();
        $this->assertContains('config:clear', $commands);
        $this->assertContains('config:cache', $commands);
        $this->assertContains('route:clear', $commands);
        $this->assertContains('route:cache', $commands);
        $this->assertContains('view:clear', $commands);
        $this->assertContains('view:cache', $commands);
    }

    public function test_optimize_system_returns_results(): void
    {
        $service = app(SystemHealthService::class);
        $result = $service->optimizeSystem();

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['logs']);

        // Verify expected commands are present
        $commands = collect($result['logs'])->pluck('command')->toArray();
        $this->assertContains('optimize', $commands);
        $this->assertContains('system-optimization-check', $commands);
    }

    public function test_all_optimization_actions_produce_logs(): void
    {
        $service = app(SystemHealthService::class);

        // Each optimization action should produce at least one log entry
        $clearLogs = $service->clearCache()['logs'];
        $this->assertGreaterThanOrEqual(4, count($clearLogs));

        $rebuildLogs = $service->rebuildCache()['logs'];
        $this->assertGreaterThanOrEqual(6, count($rebuildLogs));

        $optimizeLogs = $service->optimizeSystem()['logs'];
        $this->assertGreaterThanOrEqual(2, count($optimizeLogs));
    }

    public function test_system_health_check_runs_without_errors(): void
    {
        $service = app(SystemHealthService::class);
        $checks = $service->runAllChecks();

        $this->assertIsArray($checks);
        $this->assertCount(13, $checks);

        // Every check should have required keys
        foreach ($checks as $key => $check) {
            $this->assertArrayHasKey('status', $check, "Check {$key} missing status");
            $this->assertArrayHasKey('label', $check, "Check {$key} missing label");
            $this->assertArrayHasKey('details', $check, "Check {$key} missing details");
            $this->assertContains($check['status'], ['healthy', 'warning', 'critical'], "Check {$key} has invalid status: {$check['status']}");
        }
    }

    public function test_overall_status_is_computed_correctly(): void
    {
        $service = app(SystemHealthService::class);
        $checks = $service->runAllChecks();
        $overall = $service->getOverallStatus($checks);

        $this->assertIsArray($overall);
        $this->assertArrayHasKey('overall', $overall);
        $this->assertArrayHasKey('healthy', $overall);
        $this->assertArrayHasKey('warning', $overall);
        $this->assertArrayHasKey('critical', $overall);
        $this->assertArrayHasKey('total', $overall);
        $this->assertEquals(13, $overall['total']);
        $this->assertContains($overall['overall'], ['healthy', 'warning', 'critical']);
    }
}
