<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageRecipient;
use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiV1PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $librarian;
    protected User $assistant;
    protected User $student;
    protected User $lecturer;
    protected User $guest;
    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
        $this->librarian = User::where('email', 'librarian@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('librarian');
        $this->superAdmin = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('super-admin');
        $this->assistant = User::where('email', 'assistant@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('assistant-librarian');
        $this->lecturer = User::where('email', 'lecturer@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('lecturer');
        $this->guest = User::where('email', 'guest@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('guest');

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

    public static function roleEndpointProvider(): array
    {
        return [
            'guest no view-dashboard' => ['guest', '/api/v1/dashboard', 'GET', 403],
            'student can view-dashboard' => ['student', '/api/v1/dashboard', 'GET', 200],
            'librarian can view-dashboard' => ['librarian', '/api/v1/dashboard', 'GET', 200],
            'guest can view-books' => ['guest', '/api/v1/books', 'GET', 200],
            'student can view-books' => ['student', '/api/v1/books', 'GET', 200],
            'librarian can view-books' => ['librarian', '/api/v1/books', 'GET', 200],
            'student no borrow-books' => ['student', '/api/v1/loans/issue', 'POST', 403],
            'lecturer no borrow-books' => ['lecturer', '/api/v1/loans/issue', 'POST', 403],
            'librarian can borrow-books' => ['librarian', '/api/v1/loans/issue', 'POST', 201],
            'assistant can borrow-books' => ['assistant-librarian', '/api/v1/loans/issue', 'POST', 201],
            'student no return-books' => ['student', '/api/v1/loans/return', 'POST', 403],
            'librarian can return-books' => ['librarian', '/api/v1/loans/return', 'POST', 422],
            'assistant can return-books' => ['assistant-librarian', '/api/v1/loans/return', 'POST', 422],
            'guest no view-library-cards' => ['guest', '/api/v1/library-card', 'GET', 403],
            'student can view-library-cards' => ['student', '/api/v1/library-card', 'GET', 200],
            'librarian can view-library-cards' => ['librarian', '/api/v1/library-card', 'GET', 200],
            'lecturer can view-library-cards' => ['lecturer', '/api/v1/library-card', 'GET', 200],
            'guest no view-digital-assets' => ['guest', '/api/v1/digital-assets', 'GET', 403],
            'student can view-digital-assets' => ['student', '/api/v1/digital-assets', 'GET', 200],
            'librarian can view-digital-assets' => ['librarian', '/api/v1/digital-assets', 'GET', 200],
            'lecturer can view-digital-assets' => ['lecturer', '/api/v1/digital-assets', 'GET', 200],
            'guest no view-recommendations' => ['guest', '/api/v1/recommendations', 'GET', 403],
            'student can view-recommendations' => ['student', '/api/v1/recommendations', 'GET', 200],
            'librarian can view-recommendations' => ['librarian', '/api/v1/recommendations', 'GET', 200],
        ];
    }

    public static function messageEndpointProvider(): array
    {
        return [
            'student no view-messages' => ['student', '/api/v1/messages', 'GET', 403],
            'librarian can view-messages' => ['librarian', '/api/v1/messages', 'GET', 200],
            'student no view-messages sent' => ['student', '/api/v1/messages/sent', 'GET', 403],
            'librarian can view-messages sent' => ['librarian', '/api/v1/messages/sent', 'GET', 200],
            'student no send-messages' => ['student', '/api/v1/messages', 'POST', 403],
            'librarian can send-messages' => ['librarian', '/api/v1/messages', 'POST', 201],
        ];
    }

    public static function loanParamEndpointProvider(): array
    {
        return [
            'student no renew-books' => ['student', 403],
            'assistant no renew-books' => ['assistant-librarian', 403],
            'librarian can renew-books' => ['librarian', 200],
        ];
    }

    public static function reservationEndpointProvider(): array
    {
        return [
            'student no manage-reservations' => ['student', 403],
            'librarian can manage-reservations' => ['librarian', 201],
            'assistant can manage-reservations' => ['assistant-librarian', 201],
        ];
    }

    public static function messageParamEndpointProvider(): array
    {
        return [
            'student no reply-messages' => ['student', 403],
            'librarian can reply-messages' => ['librarian', 200],
        ];
    }

    public static function libraryCardQrEndpointProvider(): array
    {
        return [
            'guest no view-library-cards qr' => ['guest', 403],
            'student can view-library-cards qr' => ['student', 200],
        ];
    }

    #[DataProvider('roleEndpointProvider')]
    public function test_role_access(string $role, string $endpoint, string $method, int $expectedStatus): void
    {
        $user = match ($role) {
            'super-admin' => $this->superAdmin,
            'librarian' => $this->librarian,
            'assistant-librarian' => $this->assistant,
            'student' => $this->student,
            'lecturer' => $this->lecturer,
            'guest' => $this->guest,
            default => User::factory()->create()->assignRole($role),
        };

        $token = $this->token($user);
        $headers = ['Authorization' => 'Bearer '.$token];

        if ($method === 'GET') {
            $response = $this->withHeaders($headers)->getJson($endpoint);
        } else {
            $payload = match (true) {
                str_contains($endpoint, '/loans/issue') => $this->buildIssuePayload(),
                str_contains($endpoint, '/loans/return') => ['barcode' => 'NONEXISTENT', 'condition' => 'good'],
                default => [],
            };
            $response = $this->withHeaders($headers)->postJson($endpoint, $payload);
        }

        if ($expectedStatus === 403) {
            $response->assertForbidden();
        } else {
            $this->assertNotEquals(403, $response->status(), "Expected non-403 status for role={$role} endpoint={$endpoint}");
        }
    }

    #[DataProvider('messageEndpointProvider')]
    public function test_message_endpoints(string $role, string $endpoint, string $method, int $expectedStatus): void
    {
        $user = match ($role) {
            'librarian' => $this->librarian,
            'student' => $this->student,
            default => User::factory()->create()->assignRole($role),
        };

        $token = $this->token($user);
        $headers = ['Authorization' => 'Bearer '.$token];

        if ($method === 'GET') {
            $response = $this->withHeaders($headers)->getJson($endpoint);
        } else {
            $response = $this->withHeaders($headers)->postJson($endpoint, [
                'recipient_ids' => [$this->librarian->id],
                'subject' => 'Test',
                'body' => 'Body',
                'priority' => 'normal',
                'type' => 'direct',
            ]);
        }

        if ($expectedStatus === 403) {
            $response->assertForbidden();
        } else {
            $this->assertNotEquals(403, $response->status(), "Expected non-403 for role={$role} endpoint={$endpoint}");
        }
    }

    #[DataProvider('loanParamEndpointProvider')]
    public function test_renew_loan_endpoint(string $role, int $expectedStatus): void
    {
        $user = match ($role) {
            'librarian' => $this->librarian,
            'assistant-librarian' => $this->assistant,
            'student' => $this->student,
            default => User::factory()->create()->assignRole($role),
        };

        $record = $this->getBorrowRecord();
        $url = "{$this->baseUrl}/loans/{$record->id}/renew";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($user))
            ->postJson($url);

        if ($expectedStatus === 403) {
            $response->assertForbidden();
        } else {
            $this->assertNotEquals(403, $response->status(), "Expected non-403 for role={$role} renew-loans");
        }
    }

    #[DataProvider('reservationEndpointProvider')]
    public function test_reservation_endpoint(string $role, int $expectedStatus): void
    {
        $user = match ($role) {
            'librarian' => $this->librarian,
            'assistant-librarian' => $this->assistant,
            'student' => $this->student,
            default => User::factory()->create()->assignRole($role),
        };

        $book = Book::first();
        $payload = $book ? ['book_id' => $book->id] : [];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($user))
            ->postJson("{$this->baseUrl}/reservations", $payload);

        if ($expectedStatus === 403) {
            $response->assertForbidden();
        } else {
            $this->assertNotEquals(403, $response->status(), "Expected non-403 for role={$role} reservations");
        }
    }

    #[DataProvider('messageParamEndpointProvider')]
    public function test_reply_message_endpoint(string $role, int $expectedStatus): void
    {
        $user = match ($role) {
            'librarian' => $this->librarian,
            'student' => $this->student,
            default => User::factory()->create()->assignRole($role),
        };

        $message = $this->getMessage();
        $url = "{$this->baseUrl}/messages/{$message->id}/reply";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($user))
            ->postJson($url, ['body' => 'Reply body']);

        if ($expectedStatus === 403) {
            $response->assertForbidden();
        } else {
            $this->assertNotEquals(403, $response->status(), "Expected non-403 for role={$role} reply-messages");
        }
    }

    #[DataProvider('libraryCardQrEndpointProvider')]
    public function test_library_card_qr_endpoint(string $role, int $expectedStatus): void
    {
        $user = match ($role) {
            'student' => $this->student,
            'guest' => $this->guest,
            default => User::factory()->create()->assignRole($role),
        };

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($user))
            ->getJson("{$this->baseUrl}/library-card/qr-code");

        if ($expectedStatus === 403) {
            $response->assertForbidden();
        } else {
            $this->assertNotEquals(403, $response->status(), "Expected non-403 for role={$role} library-card/qr-code");
        }
    }

    public function test_requires_auth(): void
    {
        $protectedEndpoints = [
            '/api/v1/dashboard',
            '/api/v1/books',
            '/api/v1/loans/active',
            '/api/v1/library-card',
            '/api/v1/digital-assets',
            '/api/v1/recommendations',
            '/api/v1/messages',
        ];

        foreach ($protectedEndpoints as $url) {
            $response = $this->getJson($url);
            $response->assertUnauthorized("GET {$url} should require auth");
        }
    }

    public function test_super_admin_has_full_access(): void
    {
        $endpoints = [
            '/api/v1/dashboard',
            '/api/v1/books',
            '/api/v1/library-card',
            '/api/v1/digital-assets',
            '/api/v1/recommendations',
            '/api/v1/messages',
            '/api/v1/messages/sent',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->withHeader(
                'Authorization', 'Bearer '.$this->token($this->superAdmin)
            )->getJson("{$this->baseUrl}{$endpoint}");

            $this->assertNotEquals(403, $response->status(), "super-admin should access {$endpoint}");
        }
    }

    private function getBorrowRecord(): BorrowRecord
    {
        $copy = BookCopy::where('status', 'available')->first();
        if ($copy) {
            return BorrowRecord::create([
                'user_id' => $this->student->id,
                'book_copy_id' => $copy->id,
                'borrowed_at' => now(),
                'due_at' => now()->addDays(14),
                'status' => BorrowRecord::STATUS_ACTIVE,
                'max_renewals' => 2,
            ]);
        }

        return BorrowRecord::factory()->create(['user_id' => $this->student->id]);
    }

    private function getMessage(): Message
    {
        $message = Message::create([
            'sender_id' => $this->student->id,
            'subject' => 'Test',
            'body' => 'Body',
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

        return $message;
    }

    private function buildIssuePayload(): array
    {
        $copy = BookCopy::where('status', 'available')->first();
        return [
            'user_id' => $this->student->id,
            'barcode' => $copy?->barcode ?? 'TEST-BARCODE',
        ];
    }
}
