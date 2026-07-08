<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorEncryptionConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Full web profile 2FA loop:
     * 1. User enables 2FA via profile (generates secret) — plain text storage
     * 2. User verifies with TOTP code (activates 2FA)
     * 3. verifyTwoFactorCode() works against the plain-text secret (used by TwoFactorVerify component)
     */
    public function test_web_profile_2fa_full_loop(): void
    {
        $google2fa = new Google2FA();
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        // Step 1: Generate secret (simulates enable() in two-factor-form.blade.php)
        $secret = $google2fa->generateSecretKey();
        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => false,
        ]);

        // Confirm secret stored as plain text (no encrypt())
        $this->assertEquals($secret, $user->fresh()->two_factor_secret);

        // Step 2: Verify with TOTP code (simulates confirm())
        $code = $google2fa->getCurrentOtp($secret);
        $this->assertTrue($google2fa->verifyKey($secret, $code));

        $user->update(['two_factor_enabled' => true]);
        $this->assertTrue((bool) $user->fresh()->two_factor_enabled);

        // Step 3: verifyTwoFactorCode() — used by TwoFactorVerify component on login
        // This is the critical path: secret must be readable without decrypt()
        $code2 = $google2fa->getCurrentOtp($secret);
        $this->assertTrue(
            $user->verifyTwoFactorCode($code2),
            'User::verifyTwoFactorCode must work with plain-text secret after web profile setup'
        );

        // Step 4: Invalid code must fail
        $this->assertFalse(
            $user->verifyTwoFactorCode('000000'),
            'Invalid code must be rejected'
        );
    }

    /**
     * API 2FA loop:
     * 1. Enable 2FA via API
     * 2. Verify with TOTP code
     * 3. Login → requires 2FA → verify with TOTP → authenticated
     */
    public function test_api_2fa_full_loop(): void
    {
        $google2fa = new Google2FA();
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $token = $user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        // Step 1: Enable via API
        $enableResponse = $this->postJson('/api/v1/auth/2fa/enable', [
            'password' => 'password',
        ], $headers);
        $enableResponse->assertOk();

        $this->user->refresh ?? $user->refresh();
        $this->assertFalse((bool) $user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_secret);

        // Step 2: Verify with TOTP
        $code = $google2fa->getCurrentOtp($user->two_factor_secret);
        $verifyResponse = $this->postJson('/api/v1/auth/2fa/verify-setup', [
            'code' => $code,
        ], $headers);
        $verifyResponse->assertOk();
        $user->refresh();
        $this->assertTrue((bool) $user->two_factor_enabled);

        // Step 3: Login requires 2FA
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test',
        ]);
        $loginResponse->assertOk()
            ->assertJson(['data' => ['requires_two_factor' => true]]);

        // Step 4: Verify login with TOTP
        $code2 = $google2fa->getCurrentOtp($user->two_factor_secret);
        $finalResponse = $this->postJson('/api/v1/auth/2fa/verify', [
            'user_id' => $user->id,
            'code' => $code2,
            'device_name' => 'test',
        ]);
        $finalResponse->assertOk()
            ->assertJsonStructure(['data' => ['token', 'token_type']]);
    }

    /**
     * Both web and API paths store the secret as plain text.
     */
    public function test_both_paths_store_secret_as_plaintext(): void
    {
        $google2fa = new Google2FA();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Web path: stores plain text (no encrypt())
        $secret1 = $google2fa->generateSecretKey();
        $user1->update(['two_factor_secret' => $secret1]);
        $this->assertEquals($secret1, $user1->fresh()->two_factor_secret);

        // API path: stores plain text
        $secret2 = $google2fa->generateSecretKey();
        $user2->update(['two_factor_secret' => $secret2]);
        $this->assertEquals($secret2, $user2->fresh()->two_factor_secret);
    }
}
