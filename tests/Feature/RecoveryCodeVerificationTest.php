<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecoveryCodeVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected array $recoveryCodes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');

        $this->recoveryCodes = [
            'ABCD-EFGH-IJKL',
            'MNOP-QRST-UVWX',
            '1234-5678-9ABC',
        ];

        $this->user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'TESTSECRET',
            'two_factor_recovery_codes' => $this->recoveryCodes,
        ]);
    }

    public function test_valid_recovery_code_returns_token(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/v1/auth/2fa/verify-recovery', [
            'user_id' => $this->user->id,
            'recovery_code' => 'ABCD-EFGH-IJKL',
            'device_name' => 'Test Device',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token', 'token_type', 'recovery_codes_remaining']])
            ->assertJson(['data' => ['recovery_codes_remaining' => 2]]);

        $this->user->refresh();
        $codes = $this->user->two_factor_recovery_codes;
        $this->assertNotContains('ABCD-EFGH-IJKL', $codes);
        $this->assertCount(2, $codes);
    }

    public function test_recovery_code_is_case_insensitive(): void
    {
        $response = $this->postJson('/api/v1/auth/2fa/verify-recovery', [
            'user_id' => $this->user->id,
            'recovery_code' => 'abcd-efgh-ijkl',
        ]);

        $response->assertOk();
    }

    public function test_invalid_recovery_code_returns_error(): void
    {
        $response = $this->postJson('/api/v1/auth/2fa/verify-recovery', [
            'user_id' => $this->user->id,
            'recovery_code' => 'WRNG-CODE-1234',
        ]);

        $response->assertStatus(422);
    }

    public function test_used_recovery_code_cannot_be_reused(): void
    {
        // Use the code once
        $this->postJson('/api/v1/auth/2fa/verify-recovery', [
            'user_id' => $this->user->id,
            'recovery_code' => 'MNOP-QRST-UVWX',
        ])->assertOk();

        // Try to use it again
        $response = $this->postJson('/api/v1/auth/2fa/verify-recovery', [
            'user_id' => $this->user->id,
            'recovery_code' => 'MNOP-QRST-UVWX',
        ]);

        $response->assertStatus(422);
    }

    public function test_disabled_2fa_user_cannot_use_recovery_code(): void
    {
        $this->user->update(['two_factor_enabled' => false]);

        $response = $this->postJson('/api/v1/auth/2fa/verify-recovery', [
            'user_id' => $this->user->id,
            'recovery_code' => 'ABCD-EFGH-IJKL',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_with_no_recovery_codes_gets_error(): void
    {
        $this->user->update(['two_factor_recovery_codes' => []]);

        $response = $this->postJson('/api/v1/auth/2fa/verify-recovery', [
            'user_id' => $this->user->id,
            'recovery_code' => 'ABCD-EFGH-IJKL',
        ]);

        $response->assertStatus(400);
    }
}
