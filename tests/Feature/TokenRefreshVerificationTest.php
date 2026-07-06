<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenRefreshVerificationTest extends TestCase
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

    protected function login(string $deviceName = 'Test Device'): string
    {
        $response = $this->postJson("{$this->baseUrl}/auth/login", [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => $deviceName,
        ]);
        $response->assertOk();
        return $response->json('data.token');
    }

    // ========================================================================
    // Scenario 1: Login → Refresh → Old token revoked → New token works
    // ========================================================================

    public function test_login_returns_token_with_expiry(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/login", [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'Expiry Check',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user', 'token', 'token_type', 'expires_in'],
            ]);

        $this->assertGreaterThan(0, $response->json('data.expires_in'));
    }

    public function test_refresh_returns_new_token_different_from_old(): void
    {
        $token = $this->login('Refresh Verify');

        // Verify old token works
        $this->getJson("{$this->baseUrl}/auth/user", [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        // Call refresh
        $refreshResponse = $this->postJson("{$this->baseUrl}/auth/refresh", [], [
            'Authorization' => "Bearer {$token}",
        ]);
        $refreshResponse->assertOk();

        $data = $refreshResponse->json('data');
        $newToken = $data['token'];
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($token, $newToken, 'New token must differ from old');
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertGreaterThan(0, $data['expires_in']);

        // New token works for profile
        $this->getJson("{$this->baseUrl}/auth/user", [
            'Authorization' => "Bearer {$newToken}",
        ])->assertOk();
    }

    public function test_old_token_row_deleted_after_refresh(): void
    {
        $token = $this->login('Revoke Verify');

        // Find the token's ID before refresh
        $oldHash = hash('sha256', substr($token, strpos($token, '|') + 1));
        $this->assertDatabaseHas('personal_access_tokens', ['token' => $oldHash]);

        // Refresh
        $this->postJson("{$this->baseUrl}/auth/refresh", [], [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        // Old token row is gone from the database
        $this->assertDatabaseMissing('personal_access_tokens', ['token' => $oldHash]);
    }

    public function test_new_token_row_exists_after_refresh(): void
    {
        $token = $this->login('New Token Verify');

        $refreshResponse = $this->postJson("{$this->baseUrl}/auth/refresh", [], [
            'Authorization' => "Bearer {$token}",
        ]);
        $refreshResponse->assertOk();

        $newToken = $refreshResponse->json('data.token');
        $newHash = hash('sha256', substr($newToken, strpos($newToken, '|') + 1));
        $this->assertDatabaseHas('personal_access_tokens', ['token' => $newHash]);
    }

    // ========================================================================
    // Scenario 2: Refresh with invalid/expired token → fails cleanly
    // ========================================================================

    public function test_refresh_with_nonexistent_token_returns_401(): void
    {
        $fakeToken = $this->user->createToken('temp')->plainTextToken;
        $this->user->tokens()->delete();

        $this->postJson("{$this->baseUrl}/auth/refresh", [], [
            'Authorization' => "Bearer {$fakeToken}",
        ])->assertStatus(401);
    }

    public function test_refresh_with_bogus_token_returns_401(): void
    {
        $this->postJson("{$this->baseUrl}/auth/refresh", [], [
            'Authorization' => 'Bearer 1|totally-fake-token',
        ])->assertStatus(401);
    }

    public function test_request_with_no_token_returns_401(): void
    {
        $this->getJson("{$this->baseUrl}/auth/user")->assertStatus(401);
    }

    // ========================================================================
    // Scenario 3: Concurrent requests — server handles rapid token rotation
    // ========================================================================

    public function test_rapid_requests_with_valid_token_all_succeed(): void
    {
        $token = $this->login('Concurrent OK');
        $headers = ['Authorization' => "Bearer {$token}"];

        // 3 rapid requests — all should succeed
        $this->getJson("{$this->baseUrl}/auth/user", $headers)->assertOk();
        $this->getJson("{$this->baseUrl}/auth/user", $headers)->assertOk();
        $this->getJson("{$this->baseUrl}/auth/user", $headers)->assertOk();
    }

    public function test_after_refresh_new_token_replaces_old_in_database(): void
    {
        $token = $this->login('Token Rotation');
        $oldHash = hash('sha256', substr($token, strpos($token, '|') + 1));

        // Refresh
        $refreshResponse = $this->postJson("{$this->baseUrl}/auth/refresh", [], [
            'Authorization' => "Bearer {$token}",
        ]);
        $refreshResponse->assertOk();
        $newToken = $refreshResponse->json('data.token');
        $newHash = hash('sha256', substr($newToken, strpos($newToken, '|') + 1));

        // Only the new token exists in the database
        $this->assertDatabaseMissing('personal_access_tokens', ['token' => $oldHash]);
        $this->assertDatabaseHas('personal_access_tokens', ['token' => $newHash]);

        // Exactly one token row for this user
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_multiple_logins_produce_multiple_tokens(): void
    {
        $token1 = $this->login('Device 1');
        $token2 = $this->login('Device 2');

        $this->assertNotEquals($token1, $token2);

        // Both tokens work
        $this->getJson("{$this->baseUrl}/auth/user", [
            'Authorization' => "Bearer {$token1}",
        ])->assertOk();
        $this->getJson("{$this->baseUrl}/auth/user", [
            'Authorization' => "Bearer {$token2}",
        ])->assertOk();

        // Two token rows
        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    // ========================================================================
    // Register endpoint consistency
    // ========================================================================

    public function test_register_returns_expires_in(): void
    {
        $response = $this->postJson("{$this->baseUrl}/auth/register", [
            'name' => 'Verify Expiry',
            'email' => 'verify-expiry-' . uniqid() . '@test.com',
            'phone' => '0700000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['user', 'token', 'token_type', 'expires_in'],
            ]);

        $this->assertIsInt($response->json('data.expires_in'));
        $this->assertGreaterThan(0, $response->json('data.expires_in'));
    }
}
