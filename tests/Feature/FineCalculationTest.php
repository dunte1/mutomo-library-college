<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Services\FineCalculationService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FineCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BorrowRecord $borrowRecord;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::factory()->create();

        $book = Book::create([
            'title' => 'Test Book',
            'slug' => 'test-book',
            'isbn' => '978-0-0000-0000-1',
            'status' => 'active',
        ]);

        $copy = BookCopy::create([
            'book_id' => $book->id,
            'barcode' => 'TEST-BARCODE-001',
            'status' => 'borrowed',
        ]);

        $this->borrowRecord = BorrowRecord::create([
            'user_id' => $this->user->id,
            'book_copy_id' => $copy->id,
            'borrowed_at' => now()->subDays(10),
            'due_at' => now()->subDays(3),
            'status' => BorrowRecord::STATUS_OVERDUE,
        ]);
    }

    public function test_assess_overdue_fine_uses_config_rate(): void
    {
        config(['fines.daily_rate' => 75]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $fine = $service->assessOverdueFine($this->borrowRecord);

        $this->assertEquals(225, $fine->amount);
        $this->assertEquals(Fine::STATUS_PENDING, $fine->status);
    }

    public function test_assess_overdue_fine_settings_override_config(): void
    {
        config(['fines.daily_rate' => 75]);

        $settingsService = $this->createMock(SettingsService::class);
        $settingsService->method('getCirculationRules')
            ->willReturn(['fine_per_day' => 100]);

        $notificationService = $this->createMock(NotificationService::class);

        $service = new FineCalculationService($notificationService, $settingsService);

        $fine = $service->assessOverdueFine($this->borrowRecord);

        $this->assertEquals(300, $fine->amount);
    }

    public function test_assess_lost_book_fine(): void
    {
        config(['fines.lost_book_rate' => 2000, 'fines.daily_rate' => 50]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $fine = $service->assessLostBookFine($this->borrowRecord);

        $expected = 2000 + (3 * 50);
        $this->assertEquals($expected, $fine->amount);
        $this->assertEquals(Fine::STATUS_PENDING, $fine->status);
    }

    public function test_assess_damage_fine(): void
    {
        config(['fines.damage_rate' => 750]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $fine = $service->assessDamageFine($this->borrowRecord);

        $this->assertEquals(750, $fine->amount);
        $this->assertEquals(Fine::STATUS_PENDING, $fine->status);
    }

    public function test_assess_overdue_fine_returns_existing_pending_fine(): void
    {
        $existingFine = Fine::create([
            'borrow_record_id' => $this->borrowRecord->id,
            'user_id' => $this->user->id,
            'amount' => 999,
            'status' => Fine::STATUS_PENDING,
            'reason' => 'test',
            'assessed_at' => now(),
        ]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $result = $service->assessOverdueFine($this->borrowRecord);

        $this->assertEquals($existingFine->id, $result->id);
        $this->assertEquals(999, $result->amount);
    }

    public function test_waive_fine(): void
    {
        $fine = Fine::create([
            'borrow_record_id' => $this->borrowRecord->id,
            'user_id' => $this->user->id,
            'amount' => 500,
            'status' => Fine::STATUS_PENDING,
            'reason' => 'test',
            'assessed_at' => now(),
        ]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $result = $service->waiveFine($fine->id, 'Goodwill waiver');

        $this->assertEquals(Fine::STATUS_WAIVED, $result->status);
        $this->assertEquals(500, $result->waived_amount);
    }

    public function test_pay_fine_partial(): void
    {
        $fine = Fine::create([
            'borrow_record_id' => $this->borrowRecord->id,
            'user_id' => $this->user->id,
            'amount' => 500,
            'paid_amount' => 0,
            'status' => Fine::STATUS_PENDING,
            'reason' => 'test',
            'assessed_at' => now(),
        ]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $result = $service->payFine($fine->id, 200);

        $this->assertEquals(200, $result->paid_amount);
        $this->assertEquals(Fine::STATUS_PENDING, $result->status);
    }

    public function test_pay_fine_full(): void
    {
        $fine = Fine::create([
            'borrow_record_id' => $this->borrowRecord->id,
            'user_id' => $this->user->id,
            'amount' => 500,
            'paid_amount' => 0,
            'status' => Fine::STATUS_PENDING,
            'reason' => 'test',
            'assessed_at' => now(),
        ]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $result = $service->payFine($fine->id, 500);

        $this->assertEquals(Fine::STATUS_PAID, $result->status);
        $this->assertNotNull($result->paid_at);
    }

    public function test_get_user_outstanding_fines(): void
    {
        Fine::create([
            'user_id' => $this->user->id,
            'borrow_record_id' => $this->borrowRecord->id,
            'amount' => 150,
            'status' => Fine::STATUS_PENDING,
            'reason' => 'test',
            'assessed_at' => now(),
        ]);
        Fine::create([
            'user_id' => $this->user->id,
            'borrow_record_id' => $this->borrowRecord->id,
            'amount' => 250,
            'status' => Fine::STATUS_PAID,
            'reason' => 'test',
            'assessed_at' => now(),
        ]);

        $notificationService = $this->createMock(NotificationService::class);
        $service = new FineCalculationService($notificationService);

        $outstanding = $service->getUserOutstandingFines($this->user->id);

        $this->assertCount(1, $outstanding);
        $this->assertEquals(150, $outstanding->first()->amount);
    }
}
