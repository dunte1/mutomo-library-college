<?php

namespace App\Modules\API\Controllers;

use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
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
    ) {}

    public function activeBorrows(): JsonResponse
    {
        $records = BorrowRecord::with(['bookCopy.book', 'user'])
            ->where('user_id', auth()->id())
            ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])
            ->latest()
            ->limit(100)
            ->get();

        return response()->json(['data' => $records]);
    }

    public function history(): JsonResponse
    {
        $records = BorrowRecord::with(['bookCopy.book'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(min((int) request('per_page', 15), 100));

        return response()->json($records);
    }

    public function overdue(): JsonResponse
    {
        $user = auth()->user();

        if (! $user->can('view-circulation') && request('user_id') != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $records = BorrowRecord::with(['bookCopy.book', 'user'])
            ->overdue()
            ->when(request('user_id'), fn ($q) => $q->where('user_id', request('user_id')))
            ->latest()
            ->paginate(min((int) request('per_page', 15), 100));

        return response()->json($records);
    }

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

            return response()->json(['message' => 'Book issued successfully.', 'data' => $record], 201);
        } catch (\Throwable $e) {
            Log::error('API issue book failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to issue book.'], 422);
        }
    }

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

            return response()->json(['message' => 'Book returned successfully.', 'data' => $record]);
        } catch (\Throwable $e) {
            Log::error('API return book failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to return book.'], 422);
        }
    }

    public function myFines(): JsonResponse
    {
        $fines = auth()->user()->fines()->with('borrowRecord.bookCopy.book')->latest()->get();

        return response()->json([
            'data' => $fines,
            'total_outstanding' => $fines->where('status', 'pending')->sum('amount'),
        ]);
    }
}
