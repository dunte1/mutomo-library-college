<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_healthy(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'app',
                'database',
                'cache',
                'storage',
            ],
        ]);

        $response->assertJsonPath('status', 'healthy');
    }

    public function test_laravel_up_endpoint_returns_ok(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }

    public function test_privacy_page_loads(): void
    {
        $response = $this->get('/privacy');

        $response->assertOk();
        $response->assertSee('Privacy Policy');
        $response->assertSee('Data Protection');
        $response->assertSee('Cookies');
    }

    public function test_terms_page_loads(): void
    {
        $response = $this->get('/terms');

        $response->assertOk();
        $response->assertSee('Terms of Service');
        $response->assertSee('Acceptance of Terms');
        $response->assertSee('User Responsibilities');
    }
}
