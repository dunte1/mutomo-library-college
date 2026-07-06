<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Services\FineCalculationService;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Services\FinanceService;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowOverdueFinePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BorrowRecord $borrowRecord;
    protected FineCalculationService $fineService;
    protected FinanceService $financeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $book = Book::create([
            'title' => 'Integration Test Book',
            'slug' => 'integration-test-book',
            'isbn' => '978-0-0000-0000-1',
            'status' => 'active',
        ]);

        $copy = BookCopy::create([
            'book_id' => $book->id,
            'barcode' => 'INT-BARCODE-001',
            'status' => 'borrowed',
        ]);

        $this->borrowRecord = BorrowRecord::create([
            'user_id' => $this->user->id,
            'book_copy_id' => $copy->id,
            'borrowed_at' => now()->subDays(14),
            'due_at' => now()->subDays(7),
            'status' => BorrowRecord::STATUS_OVERDUE,
        ]);

        $notificationMock = $this->createMock(NotificationService::class);
        $notificationMock->method('sendFineAssessed')->willReturn(null);

        $this->fineService = new FineCalculationService($notificationMock);
        $this->financeService = app(FinanceService::class);
    }

    public function test_full_borrow_overdue_fine_assessment_flow(): void
    {
        $fine = $this->fineService->assessOverdueFine($this->borrowRecord);

        $this->assertInstanceOf(Fine::class, $fine);
        $this->assertSame($this->borrowRecord->id, $fine->borrow_record_id);
        $this->assertSame($this->user->id, $fine->user_id);
        $this->assertSame(Fine::STATUS_PENDING, $fine->status);
        $this->assertGreaterThan(0, $fine->amount);
        $this->assertStringContainsString('Overdue fine', $fine->reason);
    }

    public function test_assess_overdue_fine_deduplicates_existing_pending_fine(): void
    {
        $first = $this->fineService->assessOverdueFine($this->borrowRecord);
        $second = $this->fineService->assessOverdueFine($this->borrowRecord);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Fine::where('borrow_record_id', $this->borrowRecord->id)->count());
    }

    public function test_assess_lost_book_fine(): void
    {
        $fine = $this->fineService->assessLostBookFine($this->borrowRecord);

        $this->assertSame($this->user->id, $fine->user_id);
        $this->assertSame(Fine::STATUS_PENDING, $fine->status);
        $this->assertStringContainsString('Lost book', $fine->reason);
    }

    public function test_assess_damage_fine(): void
    {
        $fine = $this->fineService->assessDamageFine($this->borrowRecord);

        $this->assertSame($this->user->id, $fine->user_id);
        $this->assertSame(Fine::STATUS_PENDING, $fine->status);
        $this->assertStringContainsString('Damaged book', $fine->reason);
        $this->assertGreaterThan(0, $fine->amount);
    }

    public function test_partial_fine_payment(): void
    {
        $fine = $this->fineService->assessOverdueFine($this->borrowRecord);
        $partialAmount = round($fine->amount / 2, 2);

        $updated = $this->fineService->payFine($fine->id, $partialAmount);

        $this->assertSame($partialAmount, (float) $updated->paid_amount);
        $this->assertSame(Fine::STATUS_PENDING, $updated->status);
    }

    public function test_full_fine_payment_marks_fine_as_paid(): void
    {
        $fine = $this->fineService->assessOverdueFine($this->borrowRecord);

        $updated = $this->fineService->payFine($fine->id, (float) $fine->amount);

        $this->assertSame((float) $fine->amount, (float) $updated->paid_amount);
        $this->assertSame(Fine::STATUS_PAID, $updated->status);
        $this->assertNotNull($updated->paid_at);
    }

    public function test_fine_waive(): void
    {
        $fine = $this->fineService->assessOverdueFine($this->borrowRecord);

        $waived = $this->fineService->waiveFine($fine->id, 'Waived for testing');

        $this->assertSame(Fine::STATUS_WAIVED, $waived->status);
        $this->assertSame((float) $fine->amount, (float) $waived->waived_amount);
    }

    public function test_outstanding_fine_balance_calculation(): void
    {
        $fine = $this->fineService->assessOverdueFine($this->borrowRecord);
        $this->assertSame((float) $fine->amount, $fine->outstanding_balance);

        $this->fineService->payFine($fine->id, round($fine->amount * 0.3, 2));
        $remaining = $fine->fresh()->outstanding_balance;

        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThan((float) $fine->amount, $remaining);
    }

    public function test_finance_service_records_full_fine_payment_transaction(): void
    {
        $fine = $this->fineService->assessOverdueFine($this->borrowRecord);

        $transaction = $this->financeService->recordFinePayment(
            $fine,
            'cash',
            'INT-REF-001'
        );

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame($this->user->id, $transaction->user_id);
        $this->assertSame($fine->id, $transaction->fine_id);
        $this->assertSame('fine_payment', $transaction->type);
        $this->assertSame('cash', $transaction->payment_method);
        $this->assertSame('completed', $transaction->status);
        $this->assertSame('INT-REF-001', $transaction->reference);

        $this->assertSame(Fine::STATUS_PAID, $fine->fresh()->status);
    }

    public function test_get_user_outstanding_fines(): void
    {
        $this->fineService->assessOverdueFine($this->borrowRecord);

        $outstanding = $this->fineService->getUserOutstandingFines($this->user->id);

        $this->assertCount(1, $outstanding);
        $this->assertSame($this->borrowRecord->id, $outstanding->first()->borrow_record_id);

        $total = $this->fineService->getUserTotalOutstanding($this->user->id);
        $this->assertGreaterThan(0, $total);
    }
}
