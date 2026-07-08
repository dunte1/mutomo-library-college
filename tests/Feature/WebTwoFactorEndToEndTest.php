<?php

namespace Tests\Feature;

use App\Http\Middleware\TwoFactorMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class WebTwoFactorEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_two_factor_middleware_registered_in_web_group(): void
    {
        $webMiddleware = $this->app['router']->getMiddlewareGroups()['web'];

        $this->assertContains(
            TwoFactorMiddleware::class,
            $webMiddleware,
            'TwoFactorMiddleware must appear in the web middleware group array'
        );
    }

    public function test_two_factor_middleware_runs_before_dashboard(): void
    {
        // Confirm the middleware ordering: TwoFactorMiddleware must come BEFORE
        // the route is resolved. We verify it's in the group, not just aliased.
        $webMiddleware = $this->app['router']->getMiddlewareGroups()['web'];
        $twoFaIndex = array_search(TwoFactorMiddleware::class, $webMiddleware);

        $this->assertNotFalse($twoFaIndex, 'Middleware not found in web group');
        $this->assertIsInt($twoFaIndex);
    }

    public function test_2fa_enabled_user_cannot_reach_dashboard_without_code(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        // Login the user (credentials valid, session created)
        $this->actingAs($user);

        // Attempt to access dashboard — middleware should redirect to 2FA verify
        $response = $this->get('/dashboard');

        $redirectUrl = $response->headers->get('Location');
        $this->assertNotNull($redirectUrl, 'Expected a redirect for 2FA user');
        $this->assertStringContainsString(
            '/two-factor/verify',
            $redirectUrl,
            "2FA-enabled user should be redirected to /two-factor/verify, got: {$redirectUrl}"
        );
    }

    public function test_2fa_enabled_user_can_access_two_factor_verify_page(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $this->actingAs($user);

        // The 2FA verify page itself should NOT redirect (it's the exemption target)
        $response = $this->get('/two-factor/verify');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_2fa_disabled_user_reaches_dashboard_directly(): void
    {
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $user->update(['two_factor_enabled' => false]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        // Should NOT be redirected to 2FA verify — may get 200 or 403 (permission)
        // but must NOT be a redirect to /two-factor/verify
        $redirectUrl = $response->headers->get('Location');
        if ($redirectUrl) {
            $this->assertStringNotContainsString(
                '/two-factor/verify',
                $redirectUrl,
                'Non-2FA user should NOT be redirected to 2FA verify'
            );
        }
        // Acceptable outcomes: 200 (has permission) or 403 (no permission)
        $this->assertContains($response->getStatusCode(), [200, 403]);
    }

    public function test_2fa_verified_session_allows_dashboard_access(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $this->actingAs($user);

        // Simulate completed 2FA verification by setting session flag
        $this->withSession(['two_factor_verified' => true]);

        $response = $this->get('/dashboard');

        // Should NOT redirect to 2FA verify
        $redirectUrl = $response->headers->get('Location');
        if ($redirectUrl) {
            $this->assertStringNotContainsString(
                '/two-factor/verify',
                $redirectUrl,
                'Verified 2FA user should pass through to dashboard'
            );
        }
        $this->assertContains($response->getStatusCode(), [200, 403]);
    }
}
