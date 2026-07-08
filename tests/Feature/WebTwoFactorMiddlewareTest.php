<?php

namespace Tests\Feature;

use App\Http\Middleware\TwoFactorMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class WebTwoFactorMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_two_factor_middleware_is_in_web_middleware_group(): void
    {
        $webMiddleware = $this->app['router']->getMiddlewareGroups()['web'];

        $this->assertContains(
            TwoFactorMiddleware::class,
            $webMiddleware,
            'TwoFactorMiddleware must be in the web middleware group to enforce 2FA on web routes'
        );
    }

    public function test_2fa_enabled_user_is_redirected_to_verify_on_protected_route(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('two-factor.verify'));
    }

    public function test_2fa_verified_user_can_access_protected_route(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        // Simulate that the user has verified 2FA
        $response = $this->actingAs($user)
            ->withSession(['two_factor_verified' => true])
            ->get('/dashboard');

        // Should not redirect to 2FA verify (may redirect to other places based on role/permissions)
        $this->assertNotEquals(
            route('two-factor.verify'),
            $response->headers->get('Location'),
            'User with 2FA verified should NOT be redirected to two-factor.verify'
        );
    }

    public function test_2fa_disabled_user_can_access_protected_route_without_verification(): void
    {
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $user->update(['two_factor_enabled' => false]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Should not redirect to 2FA verify
        $this->assertNotEquals(
            route('two-factor.verify'),
            $response->headers->get('Location'),
            'User without 2FA should NOT be redirected to two-factor.verify'
        );
    }

    public function test_verify_two_factor_code_works_with_plain_text_secret(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        // Store secret as plain text (matching the web profile form's behavior after fix)
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $code = $google2fa->getCurrentOtp($secret);

        // This is what TwoFactorVerify::verify() calls
        $this->assertTrue(
            $user->verifyTwoFactorCode($code),
            'User::verifyTwoFactorCode() must verify a valid TOTP code against a plain-text secret'
        );

        $this->assertFalse(
            $user->verifyTwoFactorCode('000000'),
            'User::verifyTwoFactorCode() must reject an invalid code'
        );
    }
}
