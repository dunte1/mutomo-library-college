<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Settings\Models\Setting;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class ApiV1AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $student;
    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('super-admin');
        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
    }

    protected function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function headers(User $user): array
    {
        return ['Authorization' => 'Bearer '.$this->token($user)];
    }

    // ===== LOGIN =====

    public function test_login_succeeds(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/login", [
            'email' => $this->student->email,
            'password' => 'password',
            'device_name' => 'Test Phone',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user', 'token', 'token_type', 'expires_in'],
            ]);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/login", [
            'email' => 'wrong@email.com',
            'password' => 'wrongpass',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_for_inactive_user(): void
    {
        $this->student->update(['is_active' => false]);

        $response = $this->postJson("{$this->baseUrl}/auth/login", [
            'email' => $this->student->email,
            'password' => 'password',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'deactivated'));
    }

    public function test_login_rate_limited(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $response = $this->postJson("{$this->baseUrl}/auth/login", [
                'email' => 'test@test.com',
                'password' => 'wrong',
            ]);
        }

        $response->assertStatus(429);
    }

    // ===== REGISTER =====

    public function test_register_succeeds(): void
    {
        Setting::updateOrCreate(
            ['key' => 'trial_days'],
            ['value' => '0', 'group' => 'subscriptions', 'type' => 'integer']
        );

        $response = $this->postJson("{$this->baseUrl}/auth/register", [
            'name' => 'New Test User',
            'email' => 'newuser@test.com',
            'phone' => '+254712345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'student',
            'device_name' => 'Test Phone',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['user', 'token', 'token_type'],
                'message',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
        $this->assertDatabaseHas('members', ['email' => 'newuser@test.com']);
    }

    public function test_register_validates_unique_email(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/register", [
            'name' => 'Duplicate',
            'email' => $this->student->email,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'student',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_validates_password_confirmation(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/register", [
            'name' => 'No Confirm',
            'email' => 'noconfirm@test.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass!',
            'role' => 'student',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_validates_role(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/register", [
            'name' => 'Bad Role',
            'email' => 'badrole@test.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'invalid-role',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    // ===== LOGOUT =====

    public function test_logout_revokes_token(): void
    {
        $response = $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/logout");

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertCount(0, $this->student->tokens);
    }

    public function test_logout_requires_auth(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/logout");

        $response->assertUnauthorized();
    }

    // ===== GET USER / PROFILE =====

    public function test_get_user_profile(): void
    {
        $response = $this->withHeaders($this->headers($this->student))
            ->getJson("{$this->baseUrl}/auth/user");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'roles'],
            ]);
    }

    // ===== TOKEN REFRESH =====

    public function test_token_refresh_issues_new_token(): void
    {
        $token = $this->token($this->student);
        $originalTokenId = $this->student->tokens()->first()->id;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("{$this->baseUrl}/auth/refresh");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'expires_in'],
            ]);

        // Old token should be deleted
        $this->assertNull($this->student->tokens()->find($originalTokenId));
    }

    // ===== CHANGE PASSWORD =====

    public function test_change_password_succeeds(): void
    {
        $newPassword = 'NewPass123!';

        $response = $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/change-password", [
                'current_password' => 'password',
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPassword,
            ]);

        $response->assertOk()
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'Password changed'));

        $this->assertTrue(Hash::check($newPassword, $this->student->fresh()->password));
    }

    public function test_change_password_fails_with_wrong_current(): void
    {
        $response = $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/change-password", [
                'current_password' => 'wrongpassword',
                'new_password' => 'NewPass123!',
                'new_password_confirmation' => 'NewPass123!',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    // ===== FORGOT / RESET PASSWORD =====

    public function test_forgot_password_sends_reset_link(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/forgot-password", [
            'email' => $this->student->email,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'reset link'));
    }

    public function test_forgot_password_fails_for_missing_email(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/forgot-password", [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_password_succeeds_with_valid_token(): void
    {
        // Create a password reset token
        $token = Password::createToken($this->student);

        $response = $this->postJson("{$this->baseUrl}/auth/reset-password", [
            'token' => $token,
            'email' => $this->student->email,
            'password' => 'NewResetPass123!',
            'password_confirmation' => 'NewResetPass123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'reset'));
    }

    public function test_reset_password_fails_with_bad_token(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/reset-password", [
            'token' => 'invalid-token',
            'email' => $this->student->email,
            'password' => 'NewResetPass123!',
            'password_confirmation' => 'NewResetPass123!',
        ]);

        $response->assertStatus(400);
    }

    // ===== EMAIL VERIFICATION =====

    public function test_resend_verification(): void
    {
        $user = User::factory()->unverified()->create()->assignRole('student');

        $response = $this->withHeaders($this->headers($user))
            ->postJson("{$this->baseUrl}/auth/resend-verification");

        $response->assertOk()
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'sent'));
    }

    public function test_resend_verification_for_verified_email(): void
    {
        $response = $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/resend-verification");

        $response->assertOk()
            ->assertJsonPath('message', 'Email is already verified.');
    }

    // ===== PROFILE UPDATE =====

    public function test_update_profile(): void
    {
        $response = $this->withHeaders($this->headers($this->student))
            ->putJson("{$this->baseUrl}/profile", [
                'name' => 'Updated Name',
                'phone' => '+254722000000',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertEquals('Updated Name', $this->student->fresh()->name);
    }

    public function test_update_profile_with_notification_preferences(): void
    {
        $response = $this->withHeaders($this->headers($this->student))
            ->putJson("{$this->baseUrl}/profile", [
                'notification_preferences' => [
                    'in_app' => true,
                    'email' => false,
                    'push' => false,
                    'sms' => true,
                ],
            ]);

        $response->assertOk();
        $prefs = $this->student->fresh()->notification_preferences;
        $this->assertFalse($prefs['email']);
        $this->assertTrue($prefs['sms']);
    }

    // ===== 2FA =====

    public function test_2fa_enable_succeeds(): void
    {
        $response = $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/2fa/enable", [
                'password' => 'password',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['secret', 'qr_code_url', 'recovery_codes'],
            ]);

        $this->assertTrue($this->student->fresh()->two_factor_enabled);
    }

    public function test_2fa_disable_with_invalid_code(): void
    {
        $this->student->update([
            'two_factor_secret' => 'test-secret',
            'two_factor_recovery_codes' => ['code1', 'code2'],
        ]);

        // Enable 2FA first (controller sets the flag)
        $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/2fa/enable", [
                'password' => 'password',
            ]);

        $response = $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/2fa/disable", [
                'password' => 'password',
                'code' => '111111',
            ]);

        $response->assertStatus(422);
    }

    public function test_2fa_enable_fails_if_already_enabled(): void
    {
        $this->student->update(['two_factor_enabled' => true]);

        $response = $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/2fa/enable", [
                'password' => 'password',
            ]);

        $response->assertStatus(400);
    }

    // ===== 2FA VERIFY =====

    public function test_2fa_verify_with_valid_code(): void
    {
        // Enable 2FA first
        $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/2fa/enable", [
                'password' => 'password',
            ]);

        $this->assertTrue($this->student->fresh()->two_factor_enabled);
        $this->assertNotNull($this->student->fresh()->two_factor_secret);

        $google2fa = app(Google2FA::class);
        $validCode = $google2fa->getCurrentOtp($this->student->fresh()->two_factor_secret);

        $response = $this->postJson("{$this->baseUrl}/auth/2fa/verify", [
            'user_id' => $this->student->id,
            'code' => $validCode,
            'device_name' => 'Test Phone',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['token', 'token_type'],
            ]);
    }

    public function test_2fa_verify_with_invalid_code(): void
    {
        // Enable 2FA first
        $this->withHeaders($this->headers($this->student))
            ->postJson("{$this->baseUrl}/auth/2fa/enable", [
                'password' => 'password',
            ]);

        $this->assertTrue($this->student->fresh()->two_factor_enabled);

        $response = $this->postJson("{$this->baseUrl}/auth/2fa/verify", [
            'user_id' => $this->student->id,
            'code' => '000000',
            'device_name' => 'Test Phone',
        ]);

        $response->assertStatus(422);
    }
}
