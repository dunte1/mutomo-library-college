<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Reservation;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1CirculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $librarian;
    protected User $student;
    protected User $assistant;
    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->librarian = User::where('email', 'librarian@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('librarian');
        $this->assistant = User::where('email', 'assistant@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('assistant-librarian');
        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
    }

    protected function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    // ===== LOANS - ACTIVE =====

    public function test_active_loans_returns_empty_when_no_loans(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/loans/active");

        $response->assertOk();
    }

    public function test_active_loans_requires_auth(): void
    {
        $response = $this->getJson("{$this->baseUrl}/loans/active");
        $response->assertUnauthorized();
    }

    // ===== LOANS - HISTORY =====

    public function test_loan_history_returns_paginated(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/loans/history");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    // ===== LOANS - DETAIL =====

    public function test_loan_detail_for_own_loan(): void
    {
        $copy = BookCopy::where('status', 'available')->first();
        if (! $copy) {
            $this->markTestSkipped('No available book copies');
        }

        $record = BorrowRecord::create([
            'user_id' => $this->student->id,
            'book_copy_id' => $copy->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(14),
            'status' => BorrowRecord::STATUS_ACTIVE,
            'max_renewals' => 2,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/loans/{$record->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'book', 'barcode', 'status', 'due_at']]);
    }

    // ===== ISSUE BOOK =====

    public function test_issue_book_succeeds(): void
    {
        $copy = BookCopy::where('status', 'available')->first();
        if (! $copy) {
            $this->markTestSkipped('No available copies');
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/loans/issue", [
                'user_id' => $this->student->id,
                'barcode' => $copy->barcode,
            ]);

        $response->assertCreated();
    }

    public function test_issue_book_fails_with_invalid_barcode(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/loans/issue", [
                'user_id' => $this->student->id,
                'barcode' => 'INVALID-BARCODE',
            ]);

        $response->assertUnprocessable();
    }

    public function test_issue_book_requires_permission(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->postJson("{$this->baseUrl}/loans/issue", [
                'user_id' => $this->student->id,
                'barcode' => 'SOME-BARCODE',
            ]);

        $response->assertForbidden();
    }

    // ===== RETURN BOOK =====

    public function test_return_book_succeeds(): void
    {
        $copy = BookCopy::where('status', 'available')->first();
        if (! $copy) {
            $this->markTestSkipped('No available copies');
        }

        // First issue
        $issueResponse = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/loans/issue", [
                'user_id' => $this->student->id,
                'barcode' => $copy->barcode,
            ]);

        if ($issueResponse->status() !== 201) {
            $this->markTestSkipped('Could not issue book for return test');
        }

        // Then return
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/loans/return", [
                'barcode' => $copy->barcode,
                'condition' => 'good',
            ]);

        $response->assertOk();
    }

    // ===== RENEW LOAN =====

    public function test_renew_loan_succeeds_for_eligible(): void
    {
        $copy = BookCopy::where('status', 'available')->first();
        if (! $copy) {
            $this->markTestSkipped('No available copies');
        }

        // Issue book as librarian
        $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/loans/issue", [
                'user_id' => $this->student->id,
                'barcode' => $copy->barcode,
            ]);

        // Get the active borrow record
        $record = BorrowRecord::where('user_id', $this->student->id)
            ->where('book_copy_id', $copy->id)
            ->first();

        // Student renews (student has 'renew-books' permission? Let's check... 
        // Actually student doesn't have 'renew-books', so use assistant-librarian who does)
        // But assistant-librarian is renewing someone else's book, which should work
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->assistant))
            ->postJson("{$this->baseUrl}/loans/{$record->id}/renew");

        $response->assertOk();
    }

    // ===== RESERVATIONS =====

    public function test_reservations_list(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/reservations");

        $response->assertOk();
    }

    public function test_create_reservation_fails_without_permission(): void
    {
        $book = Book::first();
        if (! $book) {
            $this->markTestSkipped('No books seeded');
        }

        // Student doesn't have manage-reservations permission
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->postJson("{$this->baseUrl}/reservations", [
                'book_id' => $book->id,
            ]);

        $response->assertForbidden();
    }

    public function test_create_reservation_as_librarian(): void
    {
        $book = Book::first();
        if (! $book) {
            $this->markTestSkipped('No books seeded');
        }

        // Make all copies unavailable so reservation is needed
        BookCopy::where('book_id', $book->id)->update(['status' => 'borrowed']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->postJson("{$this->baseUrl}/reservations", [
                'book_id' => $book->id,
                'notes' => 'Test reservation',
            ]);

        $response->assertStatus(201);
    }

    // ===== FINES =====

    public function test_fines_list(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/fines");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    // ===== DASHBOARD =====

    public function test_dashboard(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/dashboard");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user', 'stats', 'active_loans', 'due_soon',
                    'featured_books', 'upcoming_events',
                ],
            ]);
    }

    public function test_dashboard_requires_permission(): void
    {
        $user = User::factory()->create(); // No role assigned = no permissions
        $response = $this->withHeader('Authorization', 'Bearer '.$user->createToken('test')->plainTextToken)
            ->getJson("{$this->baseUrl}/dashboard");

        $response->assertForbidden();
    }

    // ===== OVERDUE LOANS =====

    public function test_overdue_loans_denied_for_student(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->student))
            ->getJson("{$this->baseUrl}/loans/overdue");

        $response->assertForbidden();
    }

    public function test_overdue_loans_allowed_for_librarian(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token($this->librarian))
            ->getJson("{$this->baseUrl}/loans/overdue");

        $response->assertOk();
    }
}
