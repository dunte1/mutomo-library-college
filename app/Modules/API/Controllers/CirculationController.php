<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Services\BorrowingService;
use App\Modules\Circulation\Services\FineCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class CirculationController extends Controller
{
    public function __construct(
        protected BorrowingService $borrowingService,
        protected FineCalculationService $fineService,
        protected ApiResponseService $response,
    ) {}

    /**
     * Get the authenticated user's active borrows.
     */
    public function activeBorrows(): JsonResponse
    {
        $records = BorrowRecord::with(['bookCopy.book.authors', 'bookCopy.book.category'])
            ->where('user_id', auth()->id())
            ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($record) => $this->formatBorrowRecord($record));

        return $this->response->success($records);
    }

    /**
     * Get the authenticated user's borrow history.
     */
    public function history(): JsonResponse
    {
        $data = request()->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'status' => 'sometimes|string|in:active,returned,overdue,lost,damaged',
        ]);

        $query = BorrowRecord::with(['bookCopy.book.authors'])
            ->where('user_id', auth()->id());

        if (! empty($data['status'])) {
            if ($data['status'] === 'active') {
                $query->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE]);
            } else {
                $query->where('status', $data['status']);
            }
        }

        $records = $query->latest('borrowed_at')
            ->paginate(min((int) ($data['per_page'] ?? 15), 100));

        $records->getCollection()->transform(fn ($record) => $this->formatBorrowRecord($record));

        return $this->response->paginated($records);
    }

    /**
     * Get a single loan by ID.
     */
    public function show(int $id): JsonResponse
    {
        $record = BorrowRecord::with([
            'bookCopy.book.authors',
            'bookCopy.book.category',
            'user',
            'fine',
            'issuedBy',
            'receivedBy',
        ])->where('user_id', auth()->id())
            ->findOrFail($id);

        return $this->response->success($this->formatBorrowDetail($record));
    }

    /**
     * Renew a loan.
     */
    public function renew(int $id): JsonResponse
    {
        try {
            $record = $this->borrowingService->renewBook($id);

            return $this->response->success(
                $this->formatBorrowRecord($record),
                'Loan renewed successfully. New due date: '.$record->due_at->format('Y-m-d').'.'
            );
        } catch (\RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }
    }

    /**
     * Get overdue borrows (admin view).
     */
    public function overdue(): JsonResponse
    {
        $data = request()->validate([
            'user_id' => 'sometimes|integer|exists:users,id',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $user = auth()->user();

        if (! $user->can('view-circulation') && ($data['user_id'] ?? null) != $user->id) {
            return $this->response->forbidden();
        }

        $records = BorrowRecord::with(['bookCopy.book', 'user'])
            ->overdue()
            ->when($data['user_id'] ?? null, fn ($q) => $q->where('user_id', $data['user_id']))
            ->latest()
            ->paginate(min((int) ($data['per_page'] ?? 15), 100));

        $records->getCollection()->transform(fn ($record) => $this->formatBorrowRecord($record));

        return $this->response->paginated($records, ['total_overdue' => BorrowRecord::overdue()->count()]);
    }

    /**
     * Issue a book (admin/librarian).
     */
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barcode' => 'required|string|exists:book_copies,barcode',
        ]);

        try {
            $copy = BookCopy::where('barcode', $validated['barcode'])->firstOrFail();

            $record = $this->borrowingService->issueBook(
                $validated['user_id'],
                $copy->id,
            );

            return $this->response->created(
                $this->formatBorrowRecord($record),
                'Book issued successfully.'
            );
        } catch (\Throwable $e) {
            Log::error('API issue book failed', ['error' => $e->getMessage()]);

            return $this->response->error('Failed to issue book: '.$e->getMessage(), 422);
        }
    }

    /**
     * Return a book (admin/librarian).
     */
    public function returnBook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => 'required|string|exists:book_copies,barcode',
            'condition' => 'nullable|in:good,damaged,lost',
        ]);

        try {
            $copy = BookCopy::where('barcode', $validated['barcode'])->firstOrFail();
            $borrow = BorrowRecord::where('book_copy_id', $copy->id)
                ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])
                ->firstOrFail();

            $record = $this->borrowingService->returnBook($borrow->id, $validated['condition'] ?? 'good');

            return $this->response->success(
                $this->formatBorrowRecord($record),
                'Book returned successfully.'
            );
        } catch (\Throwable $e) {
            Log::error('API return book failed', ['error' => $e->getMessage()]);

            return $this->response->error('Failed to return book: '.$e->getMessage(), 422);
        }
    }

    /**
     * Get the authenticated user's fines.
     */
    public function myFines(): JsonResponse
    {
        $fines = auth()->user()->fines()
            ->with('borrowRecord.bookCopy.book')
            ->latest()
            ->get();

        $totalOutstanding = $fines->where('status', 'pending')->sum('outstanding_balance');

        return $this->response->success(
            $fines->map(fn ($fine) => [
                'id' => $fine->id,
                'borrow_record_id' => $fine->borrow_record_id,
                'book_title' => $fine->borrowRecord?->bookCopy?->book?->title,
                'type' => $fine->type,
                'amount' => (float) $fine->amount,
                'paid_amount' => (float) $fine->paid_amount,
                'waived_amount' => (float) $fine->waived_amount,
                'outstanding_balance' => (float) $fine->outstanding_balance,
                'status' => $fine->status,
                'reason' => $fine->reason,
                'assessed_at' => $fine->assessed_at?->toIso8601String(),
                'paid_at' => $fine->paid_at?->toIso8601String(),
            ]),
            extra: [
                'meta' => [
                    'total_outstanding' => (float) $totalOutstanding,
                    'total_paid' => (float) $fines->where('status', 'paid')->sum('paid_amount'),
                    'total_waived' => (float) $fines->where('status', 'waived')->sum('waived_amount'),
                ],
            ]
        );
    }

    /**
     * Pay an outstanding fine for the authenticated user.
     */
    public function payFine(int $id): JsonResponse
    {
        $fine = Fine::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($fine->outstanding_balance <= 0) {
            return $this->response->error('Fine is already fully paid.', 422);
        }

        try {
            $updatedFine = $this->fineService->payFine($id, (float) $fine->outstanding_balance);

            return $this->response->success([
                'id' => $updatedFine->id,
                'status' => $updatedFine->status,
                'paid_amount' => (float) $updatedFine->paid_amount,
                'outstanding_balance' => (float) $updatedFine->outstanding_balance,
            ], 'Fine payment processed.');
        } catch (\Throwable $e) {
            return $this->response->error('Failed to process fine payment: '.$e->getMessage(), 422);
        }
    }

    /**
     * Format a borrow record for API response.
     */
    protected function formatBorrowRecord(BorrowRecord $record): array
    {
        $book = $record->bookCopy?->book;

        return [
            'id' => $record->id,
            'book' => $book ? [
                'id' => $book->id,
                'title' => $book->title,
                'cover_image' => $book->cover_image ? url('storage/'.$book->cover_image) : null,
                'authors' => $book->relationLoaded('authors') ? $book->authors->map(fn ($a) => ['name' => $a->name]) : [],
            ] : null,
            'barcode' => $record->bookCopy?->barcode,
            'borrowed_at' => $record->borrowed_at?->toIso8601String(),
            'due_at' => $record->due_at?->toIso8601String(),
            'returned_at' => $record->returned_at?->toIso8601String(),
            'renewed_at' => $record->renewed_at?->toIso8601String(),
            'renewal_count' => $record->renewal_count,
            'max_renewals' => $record->max_renewals,
            'status' => $record->status,
            'days_remaining' => $record->due_at && ! $record->returned_at
                ? (int) max(0, now()->diffInDays($record->due_at, false))
                : null,
            'days_overdue' => $record->due_at && ! $record->returned_at && $record->due_at->isPast()
                ? (int) $record->due_at->diffInDays(now())
                : 0,
            'can_renew' => $record->status === BorrowRecord::STATUS_ACTIVE
                && $record->renewal_count < $record->max_renewals
                && ! $record->isOverdue(),
            'fine' => $record->relationLoaded('fine') && $record->fine ? [
                'amount' => (float) $record->fine->amount,
                'status' => $record->fine->status,
            ] : null,
        ];
    }

    /**
     * Format a borrow record with full details.
     */
    protected function formatBorrowDetail(BorrowRecord $record): array
    {
        return array_merge($this->formatBorrowRecord($record), [
            'book_copy' => $record->bookCopy ? [
                'id' => $record->bookCopy->id,
                'barcode' => $record->bookCopy->barcode,
                'shelf_location' => $record->bookCopy->shelf_location,
                'condition' => $record->bookCopy->condition,
                'status' => $record->bookCopy->status,
            ] : null,
            'issued_by' => $record->issuedBy ? [
                'id' => $record->issuedBy->id,
                'name' => $record->issuedBy->name,
            ] : null,
            'received_by' => $record->receivedBy ? [
                'id' => $record->receivedBy->id,
                'name' => $record->receivedBy->name,
            ] : null,
        ]);
    }
}
