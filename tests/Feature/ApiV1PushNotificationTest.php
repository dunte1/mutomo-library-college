<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
    }

    protected function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->token($this->student)];
    }

    public function test_subscribe_succeeds(): void
    {
        $response = $this->withHeaders($this->headers())
            ->postJson("{$this->baseUrl}/push/subscribe", [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-123',
                'keys' => [
                    'p256dh' => 'BCp1234567890abcdefghijklmnopqrstuvwxyz1234567890',
                    'auth' => 'test-auth-secret-123',
                ],
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Push subscription saved.',
            ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $this->student->id,
        ]);
    }

    public function test_subscribe_fails_without_endpoint(): void
    {
        $response = $this->withHeaders($this->headers())
            ->postJson("{$this->baseUrl}/push/subscribe", [
                'keys' => [
                    'p256dh' => 'test-key',
                    'auth' => 'test-auth',
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['endpoint']);
    }

    public function test_subscribe_fails_without_keys(): void
    {
        $response = $this->withHeaders($this->headers())
            ->postJson("{$this->baseUrl}/push/subscribe", [
                'endpoint' => 'https://fcm.test/endpoint',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['keys']);
    }

    public function test_unsubscribe_succeeds(): void
    {
        $subscription = PushSubscription::create([
            'user_id' => $this->student->id,
            'endpoint' => 'https://fcm.test/unsub-test',
            'endpoint_hash' => hash('sha256', 'https://fcm.test/unsub-test'),
            'p256dh' => 'test-p256dh-key',
            'auth' => 'test-auth-key',
        ]);

        $response = $this->withHeaders($this->headers())
            ->postJson("{$this->baseUrl}/push/unsubscribe", [
                'endpoint' => 'https://fcm.test/unsub-test',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('push_subscriptions', ['id' => $subscription->id]);
    }

    public function test_unsubscribe_with_nonexistent_endpoint(): void
    {
        $response = $this->withHeaders($this->headers())
            ->postJson("{$this->baseUrl}/push/unsubscribe", [
                'endpoint' => 'https://fcm.test/nonexistent',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_unsubscribe_all_succeeds(): void
    {
        PushSubscription::create([
            'user_id' => $this->student->id,
            'endpoint' => 'https://fcm.test/sub1',
            'endpoint_hash' => hash('sha256', 'https://fcm.test/sub1'),
            'p256dh' => 'key1',
            'auth' => 'auth1',
        ]);

        PushSubscription::create([
            'user_id' => $this->student->id,
            'endpoint' => 'https://fcm.test/sub2',
            'endpoint_hash' => hash('sha256', 'https://fcm.test/sub2'),
            'p256dh' => 'key2',
            'auth' => 'auth2',
        ]);

        $response = $this->withHeaders($this->headers())
            ->postJson("{$this->baseUrl}/push/unsubscribe-all");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(0, PushSubscription::forUser($this->student->id)->count());
    }

    public function test_unsubscribe_all_when_no_subscriptions(): void
    {
        $response = $this->withHeaders($this->headers())
            ->postJson("{$this->baseUrl}/push/unsubscribe-all");

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_subscriptions_returns_empty_when_none(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/push/subscriptions");

        $response->assertOk()
            ->assertJsonPath('count', 0);
    }

    public function test_subscriptions_returns_active_subscriptions(): void
    {
        PushSubscription::create([
            'user_id' => $this->student->id,
            'endpoint' => 'https://fcm.test/active-sub',
            'endpoint_hash' => hash('sha256', 'https://fcm.test/active-sub'),
            'p256dh' => 'active-key',
            'auth' => 'active-auth',
        ]);

        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/push/subscriptions");

        $response->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonStructure(['subscriptions', 'count']);
    }

    public function test_preferences_returns_push_disabled_when_no_subscriptions(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/push/preferences");

        $response->assertOk()
            ->assertJsonPath('push_enabled', false);
    }

    public function test_preferences_returns_push_enabled_with_subscriptions(): void
    {
        PushSubscription::create([
            'user_id' => $this->student->id,
            'endpoint' => 'https://fcm.test/pref-sub',
            'endpoint_hash' => hash('sha256', 'https://fcm.test/pref-sub'),
            'p256dh' => 'pref-key',
            'auth' => 'pref-auth',
        ]);

        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/push/preferences");

        $response->assertOk()
            ->assertJsonPath('push_enabled', true)
            ->assertJsonPath('subscription_count', 1)
            ->assertJsonStructure(['push_enabled', 'subscription_count', 'notifications_configured']);
    }

    public function test_all_endpoints_require_auth(): void
    {
        $endpoints = [
            ['POST', '/push/subscribe'],
            ['POST', '/push/unsubscribe'],
            ['POST', '/push/unsubscribe-all'],
            ['GET', '/push/subscriptions'],
            ['GET', '/push/preferences'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = match ($method) {
                'GET' => $this->getJson("{$this->baseUrl}{$uri}"),
                'POST' => $this->postJson("{$this->baseUrl}{$uri}"),
                default => $this->getJson("{$this->baseUrl}{$uri}"),
            };

            $response->assertUnauthorized("{$method} {$uri} should require auth");
        }
    }
}
