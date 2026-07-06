<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Communication\Models\Announcement;
use App\Modules\Communication\Models\Event;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageRecipient;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Models\Member;
use App\Modules\Notifications\Models\InAppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1ContentTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $librarian;
    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
        $this->librarian = User::where('email', 'librarian@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('librarian');

        if (! Member::where('user_id', $this->student->id)->exists()) {
            $nameParts = explode(' ', $this->student->name, 2);
            $member = Member::create([
                'user_id' => $this->student->id,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? $nameParts[0],
                'email' => $this->student->email,
                'admission_number' => 'OLLMCHS/2024/001',
                'department_id' => $this->student->department_id ?? 1,
                'program_id' => $this->student->program_id ?? 1,
                'membership_type' => Member::MEMBERSHIP_STUDENT,
                'status' => Member::STATUS_ACTIVE,
                'joined_at' => now(),
                'registered_by' => $this->librarian->id,
            ]);

            LibraryCard::create([
                'member_id' => $member->id,
                'card_number' => 'OLLMCHS-2026-TEST001',
                'status' => 'active',
                'issued_at' => now(),
                'expires_at' => now()->addYear(),
                'issued_by' => $this->librarian->id,
                'qr_code' => '<svg xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100"/></svg>',
                'barcode' => 'OLLMCHS-2026-TEST001',
            ]);
        }
    }

    protected function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    // ===== LIBRARY CARD =====

    public function test_library_card_returns_not_found_when_no_card(): void
    {
        $member = \App\Modules\Members\Models\Member::where('user_id', $this->student->id)->first();
        if ($member) {
            \App\Modules\Members\Models\LibraryCard::where('member_id', $member->id)->delete();
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/library-card");

        $response->assertStatus(404);
    }

    public function test_library_card_qr_code(): void
    {
        $member = \App\Modules\Members\Models\Member::where('user_id', $this->student->id)->first();
        if (! $member || ! $member->libraryCard) {
            $this->markTestSkipped('Student has no library card');
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/library-card/qr-code");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['qr_code_svg', 'card_number', 'verification_url']]);
    }

    public function test_library_card_barcode(): void
    {
        $member = \App\Modules\Members\Models\Member::where('user_id', $this->student->id)->first();
        if (! $member || ! $member->libraryCard) {
            $this->markTestSkipped('Student has no library card');
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/library-card/barcode");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['barcode', 'card_number']]);
    }

    // ===== DIGITAL ASSETS =====

    public function test_digital_assets_list(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/digital-assets");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_digital_asset_detail(): void
    {
        $asset = DigitalAsset::first();
        if (! $asset) {
            $this->markTestSkipped('No digital assets seeded');
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/digital-assets/{$asset->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'title', 'file_type']]);
    }

    // ===== READING HISTORY =====

    public function test_reading_history_empty(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/reading-history");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_update_reading_progress(): void
    {
        $asset = DigitalAsset::first();
        if (! $asset) {
            $this->markTestSkipped('No digital assets seeded');
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->putJson("{$this->baseUrl}/reading-history/{$asset->id}", [
                'progress' => 50,
                'last_page' => 100,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['progress', 'last_page']]);

        $this->assertDatabaseHas('reading_histories', [
            'user_id' => $this->student->id,
            'digital_asset_id' => $asset->id,
            'progress' => 50,
        ]);
    }

    // ===== RECOMMENDATIONS =====

    public function test_recommendations(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/recommendations");

        $response->assertOk();
    }

    // ===== MESSAGING (using librarian with proper permissions) =====

    public function test_inbox_empty(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_send_message(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages", [
                'recipient_ids' => [$this->student->id],
                'subject' => 'Test Subject',
                'body' => 'Test message body',
                'priority' => 'normal',
                'type' => 'direct',
            ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'subject', 'body']]);
    }

    public function test_sent_messages(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/sent");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_message_detail(): void
    {
        $message = Message::create([
            'sender_id' => $this->librarian->id,
            'subject' => 'Library Notice',
            'body' => 'Important notice',
            'priority' => 'normal',
            'type' => 'direct',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        MessageRecipient::create([
            'message_id' => $message->id,
            'recipient_id' => $this->librarian->id,
            'copy_type' => 'to',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'subject', 'sender', 'body']]);
    }

    public function test_reply_to_message(): void
    {
        $message = Message::create([
            'sender_id' => $this->student->id,
            'subject' => 'Question',
            'body' => 'Do you need help?',
            'priority' => 'normal',
            'type' => 'direct',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        MessageRecipient::create([
            'message_id' => $message->id,
            'recipient_id' => $this->librarian->id,
            'copy_type' => 'to',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$message->id}/reply", [
                'body' => 'Yes, thanks!',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.body', 'Yes, thanks!');
    }

    // ===== NOTIFICATIONS =====

    public function test_notifications_list(): void
    {
        InAppNotification::create([
            'user_id' => $this->student->id,
            'type' => 'system',
            'title' => 'Welcome',
            'body' => 'Welcome to the library',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/notifications");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['unread_count']]);
    }

    public function test_mark_notification_as_read(): void
    {
        $notification = InAppNotification::create([
            'user_id' => $this->student->id,
            'type' => 'system',
            'title' => 'Test',
            'body' => 'Test notification',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->postJson("{$this->baseUrl}/notifications/{$notification->id}/read");

        $response->assertOk();
        $this->assertTrue((bool) $notification->fresh()->is_read);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        InAppNotification::create(['user_id' => $this->student->id, 'type' => 'system', 'title' => 'A', 'body' => 'A']);
        InAppNotification::create(['user_id' => $this->student->id, 'type' => 'system', 'title' => 'B', 'body' => 'B']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->postJson("{$this->baseUrl}/notifications/read-all");

        $response->assertOk();
        $this->assertEquals(0, InAppNotification::where('user_id', $this->student->id)->unread()->count());
    }

    public function test_notification_unread_count(): void
    {
        InAppNotification::create(['user_id' => $this->student->id, 'type' => 'system', 'title' => 'Test', 'body' => 'Test']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/notifications/unread-count");

        $response->assertOk()
            ->assertJsonPath('data.unread_count', 1);
    }

    // ===== ANNOUNCEMENTS =====

    public function test_announcements_list(): void
    {
        Announcement::create([
            'title' => 'Library Hours',
            'content' => 'Extended hours',
            'type' => 'general',
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $this->librarian->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/announcements");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    // ===== EVENTS =====

    public function test_events_list(): void
    {
        Event::create([
            'title' => 'Research Workshop',
            'description' => 'Learn research methods',
            'location' => 'Library Room 3',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(2),
            'type' => 'workshop',
            'status' => 'published',
            'created_by' => $this->librarian->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/events");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    // ===== UNAUTHENTICATED ACCESS =====

    public function test_all_protected_endpoints_require_auth(): void
    {
        $protectedGetEndpoints = [
            "{$this->baseUrl}/profile",
            "{$this->baseUrl}/books",
            "{$this->baseUrl}/loans/active",
            "{$this->baseUrl}/fines",
            "{$this->baseUrl}/reservations",
            "{$this->baseUrl}/library-card",
            "{$this->baseUrl}/digital-assets",
            "{$this->baseUrl}/reading-history",
            "{$this->baseUrl}/recommendations",
            "{$this->baseUrl}/messages",
            "{$this->baseUrl}/notifications",
            "{$this->baseUrl}/announcements",
            "{$this->baseUrl}/events",
            "{$this->baseUrl}/dashboard",
        ];

        foreach ($protectedGetEndpoints as $url) {
            $response = $this->getJson($url);
            $response->assertUnauthorized("GET {$url} should require auth");
        }
    }
}
