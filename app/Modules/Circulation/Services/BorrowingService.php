<?php

namespace App\Modules\Circulation\Services;

use App\Models\User;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Shared\Helpers\AuditHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BorrowingService
{
    public function __construct(
        protected FineCalculationService $fineService,
    ) {}

    public function issueBook(int $userId, int $bookCopyId, ?int $issuedBy = null): BorrowRecord
    {
        return DB::transaction(function () use ($userId, $bookCopyId, $issuedBy) {
            $copy = BookCopy::findOrFail($bookCopyId);
            $user = User::findOrFail($userId);

            if (!$copy->isAvailable()) {
                throw new \RuntimeException('Book copy is not available for borrowing.');
            }

            if ($this->hasReachedBorrowLimit($user)) {
                throw new \RuntimeException('User has reached the maximum borrow limit.');
            }

            if ($this->hasOverdueItems($user)) {
                throw new \RuntimeException('User has overdue items that must be returned first.');
            }

            $borrowDuration = $this->getBorrowDuration($user);

            $record = BorrowRecord::create([
                'user_id' => $userId,
                'book_copy_id' => $bookCopyId,
                'borrowed_at' => now(),
                'due_at' => now()->addDays($borrowDuration),
                'status' => BorrowRecord::STATUS_ACTIVE,
                'max_renewals' => 2,
                'issued_by' => $issuedBy ?? auth()->id(),
                'created_by' => auth()->id(),
            ]);

            $copy->update(['status' => BookCopy::STATUS_BORROWED]);

            AuditHelper::log('book-borrowed', 'circulation', [
                'borrow_id' => $record->id,
                'user_id' => $userId,
                'book_copy_id' => $bookCopyId,
                'due_at' => $record->due_at->toDateString(),
            ]);

            return $record->load(['user', 'bookCopy.book']);
        });
    }

    public function returnBook(int $borrowId, ?string $condition = null, ?int $receivedBy = null): BorrowRecord
    {
        return DB::transaction(function () use ($borrowId, $condition, $receivedBy) {
            $record = BorrowRecord::with(['bookCopy'])->findOrFail($borrowId);

            if ($record->status === BorrowRecord::STATUS_RETURNED) {
                throw new \RuntimeException('Book has already been returned.');
            }

            $record->update([
                'returned_at' => now(),
                'received_by' => $receivedBy ?? auth()->id(),
                'status' => BorrowRecord::STATUS_RETURNED,
                'updated_by' => auth()->id(),
            ]);

            $copyData = ['status' => BookCopy::STATUS_AVAILABLE];
            if ($condition) {
                $copyData['condition'] = $condition;
            }
            $record->bookCopy->update($copyData);

            if ($record->isOverdue()) {
                $this->fineService->assessOverdueFine($record);
            }

            AuditHelper::log('book-returned', 'circulation', [
                'borrow_id' => $record->id,
                'user_id' => $record->user_id,
                'days_overdue' => $record->daysOverdue(),
            ]);

            return $record->fresh()->load(['user', 'bookCopy.book', 'fine']);
        });
    }

    public function renewBook(int $borrowId): BorrowRecord
    {
        return DB::transaction(function () use ($borrowId) {
            $record = BorrowRecord::findOrFail($borrowId);

            if ($record->status !== BorrowRecord::STATUS_ACTIVE) {
                throw new \RuntimeException('Only active borrows can be renewed.');
            }

            if ($record->renewal_count >= $record->max_renewals) {
                throw new \RuntimeException('Maximum renewal count reached.');
            }

            if ($record->isOverdue()) {
                throw new \RuntimeException('Overdue items cannot be renewed. Please return and re-borrow.');
            }

            $renewalDays = $this->getRenewalDuration();
            $record->update([
                'due_at' => now()->addDays($renewalDays),
                'renewed_at' => now(),
                'renewal_count' => $record->renewal_count + 1,
                'updated_by' => auth()->id(),
            ]);

            AuditHelper::log('book-renewed', 'circulation', [
                'borrow_id' => $record->id,
                'new_due_at' => $record->due_at->toDateString(),
                'renewal_count' => $record->renewal_count,
            ]);

            return $record->fresh()->load(['user', 'bookCopy.book']);
        });
    }

    public function markAsLost(int $borrowId): BorrowRecord
    {
        return DB::transaction(function () use ($borrowId) {
            $record = BorrowRecord::findOrFail($borrowId);

            $record->update([
                'status' => BorrowRecord::STATUS_LOST,
                'updated_by' => auth()->id(),
            ]);

            $record->bookCopy->update(['status' => BookCopy::STATUS_LOST]);

            $this->fineService->assessLostBookFine($record);

            AuditHelper::log('book-marked-lost', 'circulation', [
                'borrow_id' => $record->id,
                'book_copy_id' => $record->book_copy_id,
            ]);

            return $record->fresh()->load(['user', 'bookCopy.book', 'fine']);
        });
    }

    public function markAsDamaged(int $borrowId): BorrowRecord
    {
        return DB::transaction(function () use ($borrowId) {
            $record = BorrowRecord::findOrFail($borrowId);

            $record->update([
                'returned_at' => now(),
                'status' => BorrowRecord::STATUS_DAMAGED,
                'updated_by' => auth()->id(),
            ]);

            $record->bookCopy->update(['status' => BookCopy::STATUS_DAMAGED, 'condition' => 'poor']);

            $this->fineService->assessDamageFine($record);

            AuditHelper::log('book-marked-damaged', 'circulation', [
                'borrow_id' => $record->id,
            ]);

            return $record->fresh()->load(['user', 'bookCopy.book', 'fine']);
        });
    }

    public function hasReachedBorrowLimit(User $user): bool
    {
        $limit = $this->getBorrowLimit($user);
        $activeCount = BorrowRecord::where('user_id', $user->id)
            ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])
            ->count();

        return $activeCount >= $limit;
    }

    public function hasOverdueItems(User $user): bool
    {
        return BorrowRecord::where('user_id', $user->id)
            ->where('status', BorrowRecord::STATUS_ACTIVE)
            ->where('due_at', '<', now())
            ->exists();
    }

    public function getBorrowLimit(User $user): int
    {
        if ($user->isStudent()) return 3;
        if ($user->isLecturer()) return 5;
        return 10;
    }

    public function getBorrowDuration(User $user): int
    {
        if ($user->isStudent()) return 14;
        if ($user->isLecturer()) return 30;
        return 21;
    }

    public function getRenewalDuration(): int
    {
        return 7;
    }

    public function getActiveBorrows(int $perPage = 15): LengthAwarePaginator
    {
        return BorrowRecord::with(['user', 'bookCopy.book'])
            ->active()
            ->orderBy('borrowed_at', 'desc')
            ->paginate($perPage);
    }

    public function getOverdueBorrows(int $perPage = 15): LengthAwarePaginator
    {
        return BorrowRecord::with(['user', 'bookCopy.book'])
            ->overdue()
            ->orderBy('due_at', 'asc')
            ->paginate($perPage);
    }

    public function getBorrowHistory(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return BorrowRecord::with(['bookCopy.book'])
            ->byUser($userId)
            ->orderBy('borrowed_at', 'desc')
            ->paginate($perPage);
    }

    public function getStatistics(): array
    {
        return [
            'active_borrows' => BorrowRecord::active()->count(),
            'overdue_borrows' => BorrowRecord::overdue()->count(),
            'returned_today' => BorrowRecord::whereDate('returned_at', today())->count(),
            'borrowed_today' => BorrowRecord::whereDate('borrowed_at', today())->count(),
            'total_borrows' => BorrowRecord::count(),
        ];
    }
}
