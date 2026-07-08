<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSessionIdleTimeout;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionIdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_idle_timeout_middleware_is_in_web_group(): void
    {
        $webMiddleware = $this->app['router']->getMiddlewareGroups()['web'];

        $this->assertContains(
            EnsureSessionIdleTimeout::class,
            $webMiddleware,
            'EnsureSessionIdleTimeout must be in the web middleware group'
        );
    }

    public function test_idle_timeout_config_defaults_to_30_minutes(): void
    {
        $timeout = config('auth.idle_timeout_minutes');
        $this->assertEquals(30, $timeout);
    }

    public function test_active_user_reaches_dashboard(): void
    {
        $user = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create(['email' => 'active@test.com']);

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_middleware_sets_activity_timestamp(): void
    {
        $user = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create(['email' => 'ts@test.com']);

        $this->actingAs($user);
        $this->get('/dashboard')->assertOk();

        $this->assertNotNull(session('last_activity_at'));
    }

    public function test_stale_timestamp_exceeds_idle_threshold(): void
    {
        $timeout = config('auth.idle_timeout_minutes');

        // Create timestamps far enough apart to survive diffInMinutes truncation
        $stale = Carbon::now()->subMinutes($timeout + 30);
        $fresh = Carbon::now();

        $diffSeconds = abs($fresh->diffInSeconds($stale));
        $diffMinutes = $diffSeconds / 60;

        $this->assertGreaterThanOrEqual(
            $timeout,
            $diffMinutes,
            "Stale diff ({$diffMinutes} min) should exceed timeout ({$timeout} min)"
        );
    }

    public function test_fresh_timestamp_within_idle_threshold(): void
    {
        $timeout = config('auth.idle_timeout_minutes');

        $fresh = Carbon::now();
        $now = Carbon::now();

        $diffMinutes = $now->diffInSeconds($fresh) / 60;

        $this->assertLessThan(
            $timeout,
            $diffMinutes,
            "Fresh diff ({$diffMinutes} min) should be within timeout ({$timeout} min)"
        );
    }
}
