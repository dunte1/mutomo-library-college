<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageRecipient;
use App\Modules\Communication\Models\MessageTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1MessagingTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $student2;
    protected User $librarian;
    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
        $this->student2 = User::where('email', 'student2@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
        $this->librarian = User::where('email', 'librarian@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('librarian');

        if ($this->librarian && !$this->librarian->can('manage-templates')) {
            $role = $this->librarian->roles->first();
            if ($role) {
                $role->givePermissionTo('manage-templates');
            }
        }
    }

    protected function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function createMessage(User $sender, array $recipientIds, array $overrides = []): Message
    {
        $message = Message::create(array_merge([
            'sender_id' => $sender->id,
            'subject' => 'Test Subject',
            'body' => 'Test body content',
            'priority' => 'normal',
            'type' => 'direct',
            'status' => 'sent',
            'sent_at' => now(),
        ], $overrides));

        foreach ($recipientIds as $rid) {
            MessageRecipient::create([
                'message_id' => $message->id,
                'recipient_id' => $rid,
                'copy_type' => 'to',
            ]);
        }

        return $message;
    }

    // ===== FORWARD =====

    public function test_forward_message(): void
    {
        $msg = $this->createMessage($this->student, [$this->librarian->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$msg->id}/forward", [
                'recipient_ids' => [$this->student2->id],
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Message forwarded.');
    }

    public function test_forward_message_requires_recipients(): void
    {
        $msg = $this->createMessage($this->student, [$this->librarian->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$msg->id}/forward", []);

        $response->assertStatus(422);
    }

    // ===== SEARCH =====

    public function test_search_messages_by_subject(): void
    {
        $this->createMessage($this->student, [$this->librarian->id], ['subject' => 'UniqueLibraryNotice']);
        $this->createMessage($this->student, [$this->librarian->id], ['subject' => 'OtherMessage']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/search?q=UniqueLibraryNotice");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/search?q=ZZZZNONEXISTENT");

        $response->assertOk();
    }

    // ===== UNREAD COUNT =====

    public function test_unread_count(): void
    {
        $this->createMessage($this->librarian, [$this->student->id]);
        $this->createMessage($this->librarian, [$this->student->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/unread-count");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['unread_count']]);
    }

    public function test_unread_count_zero_when_none(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/unread-count");

        $response->assertOk()
            ->assertJsonPath('data.unread_count', 0);
    }

    // ===== MARK UNREAD =====

    public function test_mark_message_as_unread(): void
    {
        $msg = $this->createMessage($this->librarian, [$this->librarian->id]);
        $recipient = MessageRecipient::where('message_id', $msg->id)
            ->where('recipient_id', $this->librarian->id)
            ->first();
        $recipient->update(['is_read' => true, 'read_at' => now()]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$msg->id}/mark-unread");

        $response->assertOk()
            ->assertJsonPath('message', 'Message marked as unread.');
    }

    // ===== ARCHIVE / UNARCHIVE / ARCHIVED LIST =====

    public function test_archive_message(): void
    {
        $msg = $this->createMessage($this->librarian, [$this->librarian->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$msg->id}/archive");

        $response->assertOk()
            ->assertJsonPath('message', 'Message archived.');
    }

    public function test_archived_list(): void
    {
        $msg = $this->createMessage($this->librarian, [$this->librarian->id]);

        $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$msg->id}/archive");

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/archived");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_unarchive_message(): void
    {
        $msg = $this->createMessage($this->librarian, [$this->librarian->id]);

        $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$msg->id}/archive");

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/{$msg->id}/unarchive");

        $response->assertOk()
            ->assertJsonPath('message', 'Message restored from archive.');
    }

    // ===== TEMPLATES CRUD =====

    public function test_create_template(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/templates", [
                'name' => 'Overdue Notice',
                'subject' => 'Overdue: {{title}}',
                'body' => 'Please return {{title}} by {{date}}.',
                'priority' => 'high',
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Template created.');
    }

    public function test_create_template_requires_name(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/templates", [
                'subject' => 'No name',
                'body' => 'Test',
            ]);

        $response->assertStatus(422);
    }

    public function test_list_templates(): void
    {
        MessageTemplate::create([
            'created_by' => $this->librarian->id,
            'name' => 'Welcome',
            'subject' => 'Welcome!',
            'body' => 'Welcome to the library!',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/templates");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_show_template(): void
    {
        $template = MessageTemplate::create([
            'created_by' => $this->librarian->id,
            'name' => 'Welcome',
            'subject' => 'Welcome!',
            'body' => 'Welcome to the library!',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/templates/{$template->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'subject', 'body']]);
    }

    public function test_update_template(): void
    {
        $template = MessageTemplate::create([
            'created_by' => $this->librarian->id,
            'name' => 'Old Name',
            'subject' => 'Old Subject',
            'body' => 'Old body',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->putJson("{$this->baseUrl}/templates/{$template->id}", [
                'name' => 'Updated Name',
                'subject' => 'Updated Subject',
                'body' => 'Updated body',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Template updated.');
    }

    public function test_delete_template(): void
    {
        $template = MessageTemplate::create([
            'created_by' => $this->librarian->id,
            'name' => 'Delete Me',
            'subject' => 'Bye',
            'body' => 'Will be deleted',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->deleteJson("{$this->baseUrl}/templates/{$template->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Template deleted.');
    }

    public function test_apply_template(): void
    {
        $template = MessageTemplate::create([
            'created_by' => $this->librarian->id,
            'name' => 'Notice',
            'subject' => 'Notice: {{title}}',
            'body' => 'Dear {{name}}, please {{action}}.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/templates/{$template->id}/apply", [
                'variables' => [
                    'title' => 'Library Closure',
                    'name' => 'Student',
                    'action' => 'return books',
                ],
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['subject', 'body']]);
    }

    public function test_templates_require_manage_templates_permission(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->postJson("{$this->baseUrl}/templates", [
                'name' => 'Test',
                'subject' => 'Test',
                'body' => 'Test',
            ]);

        $response->assertForbidden();
    }

    // ===== INBOX CONTAINS UNREAD COUNT IN META =====

    public function test_inbox_meta_includes_unread_count(): void
    {
        $this->createMessage($this->librarian, [$this->librarian->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/inbox");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['total_unread']]);
    }

    // ===== SEND MESSAGE (POST /messages/send) =====

    public function test_send_message_creates_recipient_records(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/messages/send", [
                'recipient_ids' => [$this->student->id],
                'subject' => 'Test Direct Message',
                'body' => 'This is a test message body.',
                'priority' => 'normal',
                'type' => 'direct',
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Message sent.')
            ->assertJsonStructure(['data' => ['id', 'subject', 'sender']]);

        $messageId = $response->json('data.id');
        $this->assertDatabaseHas('message_recipients', [
            'message_id' => $messageId,
            'recipient_id' => $this->student->id,
        ]);
    }

    public function test_send_message_fails_without_send_messages_permission(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->postJson("{$this->baseUrl}/messages/send", [
                'recipient_ids' => [$this->librarian->id],
                'subject' => 'Test',
                'body' => 'Test body',
                'priority' => 'normal',
                'type' => 'direct',
            ]);

        $response->assertForbidden();
    }

    // ===== SENT MESSAGES (GET /messages/sent) =====

    public function test_sent_messages_include_sender_and_recipients(): void
    {
        $this->createMessage($this->librarian, [$this->student->id, $this->student2->id],
            ['subject' => 'SentMsgTest']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/messages/sent");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);

        $sent = collect($response->json('data'));
        $target = $sent->firstWhere('subject', 'SentMsgTest');

        $this->assertNotNull($target, 'Sent message not found in response');
        $this->assertArrayHasKey('sender', $target, 'sender key missing');
        $this->assertNotNull($target['sender'], 'sender is null — relation not loaded');
        $this->assertEquals($this->librarian->name, $target['sender']['name']);
        $this->assertArrayHasKey('recipients', $target, 'recipients key missing');
        $this->assertCount(2, $target['recipients']);
        $this->assertEquals($this->student->id, $target['recipients'][0]['recipient_id']);
    }

    public function test_sent_messages_requires_view_messages_permission(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/messages/sent");

        $response->assertForbidden();
    }

    // ===== USER SEARCH (GET /users/search) =====

    public function test_user_search_returns_matching_users(): void
    {
        // Search by name fragment
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/users/search?q=student");

        $response->assertOk()
            ->assertJsonStructure(['data']);
        $users = $response->json('data');
        $this->assertNotEmpty($users);
        // Each result should have the expected keys
        foreach ($users as $u) {
            $this->assertArrayHasKey('id', $u);
            $this->assertArrayHasKey('name', $u);
            $this->assertArrayHasKey('email', $u);
            $this->assertArrayHasKey('profile_photo_url', $u);
        }
    }

    public function test_user_search_returns_empty_for_no_match(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/users/search?q=ZZZZNONEXISTENT");

        $response->assertOk();
        $this->assertEmpty($response->json('data'));
    }

    public function test_user_search_requires_send_messages_permission(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/users/search?q=admin");

        $response->assertForbidden();
    }

    // ===== MESSAGE DETAIL (GET /messages/{id}) =====

    public function test_message_detail_includes_sender_and_replies(): void
    {
        $msg = $this->createMessage($this->librarian, [$this->student->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/messages/{$msg->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'subject', 'sender', 'body']]);
        $data = $response->json('data');
        $this->assertNotNull($data['sender']);
        $this->assertEquals($this->librarian->name, $data['sender']['name']);
    }

    // ===== UNREAD COUNT =====

    public function test_unread_count_reflects_inbox_messages(): void
    {
        $this->createMessage($this->librarian, [$this->student->id]);
        $this->createMessage($this->librarian, [$this->student->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/messages/unread-count");

        $response->assertOk();
        $this->assertGreaterThanOrEqual(2, $response->json('data.unread_count'));
    }

    // ===== INBOX MESSAGES HAVE CORRECT STRUCTURE =====

    public function test_inbox_message_has_sender(): void
    {
        $this->createMessage($this->librarian, [$this->student->id],
            ['subject' => 'InboxSenderTest']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/messages/inbox");

        $response->assertOk();
        $inbox = collect($response->json('data'));
        $target = $inbox->firstWhere('subject', 'InboxSenderTest');
        $this->assertNotNull($target);
        $this->assertArrayHasKey('sender', $target);
        $this->assertNotNull($target['sender']);
        $this->assertEquals($this->librarian->name, $target['sender']['name']);
    }
}
