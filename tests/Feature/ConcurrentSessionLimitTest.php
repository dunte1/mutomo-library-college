<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\API\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrentSessionLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_max_concurrent_sessions_config_exists(): void
    {
        $max = config('auth.max_concurrent_sessions');
        $this->assertNotNull($max);
        $this->assertIsInt($max);
        $this->assertGreaterThan(0, $max);
    }

    public function test_new_login_revokes_oldest_tokens_at_limit(): void
    {
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create();

        $maxSessions = config('auth.max_concurrent_sessions');

        // Create tokens up to the limit
        for ($i = 0; $i < $maxSessions; $i++) {
            $user->createToken("session-{$i}");
        }
        $this->assertEquals($maxSessions, $user->tokens()->count());

        // Creating one more token should revoke the oldest
        $authService = app(AuthenticationService::class);
        $authService->createToken($user, 'new-session');

        // Should still be at maxSessions (oldest was revoked, new one created)
        $this->assertEquals($maxSessions, $user->tokens()->count());
    }

    public function test_tokens_below_limit_are_not_revoked(): void
    {
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create();

        // Create fewer tokens than the limit
        $user->createToken('session-1');
        $user->createToken('session-2');

        $authService = app(AuthenticationService::class);
        $authService->createToken($user, 'session-3');

        // All 3 tokens should exist
        $this->assertEquals(3, $user->tokens()->count());
    }

    public function test_newest_token_survives_revocation(): void
    {
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create();

        $maxSessions = config('auth.max_concurrent_sessions');

        // Create tokens up to the limit
        for ($i = 0; $i < $maxSessions; $i++) {
            $user->createToken("old-session-{$i}");
        }

        $authService = app(AuthenticationService::class);
        $newToken = $authService->createToken($user, 'freshest-session');

        // The newest token should exist
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'freshest-session',
        ]);
    }
}
