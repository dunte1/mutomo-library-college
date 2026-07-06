<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorFlowTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
    }

    public function test_login_with_2fa_returns_requires_two_factor(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $this->user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $response = $this->postJson("{$this->baseUrl}/auth/login", [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => '2FA Test',
        ]);

        $response->assertOk()
            ->assertJson(['data' => ['requires_two_factor' => true]])
            ->assertJsonStructure(['data' => ['requires_two_factor', 'temp_token', 'user_id']]);
    }

    public function test_verify_2fa_with_valid_code_returns_token(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $this->user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson("{$this->baseUrl}/auth/2fa/verify", [
            'user_id' => $this->user->id,
            'code' => $code,
            'device_name' => '2FA Test',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token', 'token_type']]);

        $token = $response->json('data.token');
        $this->getJson("{$this->baseUrl}/auth/user", [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();
    }

    public function test_verify_2fa_with_invalid_code_returns_error(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $this->user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $response = $this->postJson("{$this->baseUrl}/auth/2fa/verify", [
            'user_id' => $this->user->id,
            'code' => '000000',
        ]);

        $response->assertStatus(422);
    }

    // ===== Two-step enable flow =====

    public function test_enable_2fa_generates_qr_and_codes_without_activating(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->postJson("{$this->baseUrl}/auth/2fa/enable", [
            'password' => 'password',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['secret', 'qr_code_url', 'recovery_codes']]);

        $codes = $response->json('data.recovery_codes');
        $this->assertIsArray($codes);
        $this->assertCount(8, $codes);

        // 2FA should NOT be enabled yet — verification still needed
        $this->user->refresh();
        $this->assertFalse((bool) $this->user->two_factor_enabled);
        $this->assertNotNull($this->user->two_factor_secret);
    }

    public function test_verify_setup_activates_2fa_with_valid_code(): void
    {
        $google2fa = new Google2FA();
        $token = $this->user->createToken('test')->plainTextToken;

        // Step 1: Enable (generates secret, does NOT activate)
        $this->postJson("{$this->baseUrl}/auth/2fa/enable", [
            'password' => 'password',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        $this->user->refresh();
        $this->assertFalse((bool) $this->user->two_factor_enabled);
        $secret = $this->user->two_factor_secret;

        // Step 2: Verify with valid code → activates 2FA
        $code = $google2fa->getCurrentOtp($secret);
        $this->postJson("{$this->baseUrl}/auth/2fa/verify-setup", [
            'code' => $code,
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        $this->user->refresh();
        $this->assertTrue((bool) $this->user->two_factor_enabled);
    }

    public function test_verify_setup_rejects_invalid_code(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        // Enable first
        $this->postJson("{$this->baseUrl}/auth/2fa/enable", [
            'password' => 'password',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        // Try to verify with wrong code
        $response = $this->postJson("{$this->baseUrl}/auth/2fa/verify-setup", [
            'code' => '000000',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(422);

        // Should still not be enabled
        $this->user->refresh();
        $this->assertFalse((bool) $this->user->two_factor_enabled);
    }

    public function test_verify_setup_without_enable_returns_error(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        // Try to verify without calling enable first
        $response = $this->postJson("{$this->baseUrl}/auth/2fa/verify-setup", [
            'code' => '123456',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(400);
    }

    public function test_disable_2fa_with_valid_code(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $this->user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $code = $google2fa->getCurrentOtp($secret);
        $token = $this->user->createToken('test')->plainTextToken;

        $this->postJson("{$this->baseUrl}/auth/2fa/disable", [
            'password' => 'password',
            'code' => $code,
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        $this->user->refresh();
        $this->assertFalse((bool) $this->user->two_factor_enabled);
        $this->assertNull($this->user->two_factor_secret);
    }

    public function test_login_without_2fa_returns_token_directly(): void
    {
        $this->user->update(['two_factor_enabled' => false]);

        $response = $this->postJson("{$this->baseUrl}/auth/login", [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'No 2FA Test',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['user', 'token', 'token_type', 'expires_in']])
            ->assertJsonMissing(['requires_two_factor' => true]);
    }
}
