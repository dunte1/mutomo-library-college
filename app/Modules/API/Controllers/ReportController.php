<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\ReadingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ReportController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    /**
     * Personal reading summary for the authenticated user.
     */
    public function readingSummary(): JsonResponse
    {
        $user = auth()->user();

        $totalBorrowed = BorrowRecord::where('user_id', $user->id)->count();
        $activeLoans = BorrowRecord::where('user_id', $user->id)
            ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])
            ->count();
        $completedLoans = BorrowRecord::where('user_id', $user->id)
            ->where('status', BorrowRecord::STATUS_RETURNED)
            ->count();
        $overdueCount = BorrowRecord::where('user_id', $user->id)->overdue()->count();

        $totalFines = (float) Fine::where('user_id', $user->id)->sum('amount');
        $pendingFines = (float) Fine::where('user_id', $user->id)->pending()->sum('amount');

        $digitalReadCount = ReadingHistory::where('user_id', $user->id)->count();
        $digitalAssetsDownloaded = $user->downloads()->where('downloadable_type', DigitalAsset::class)->count();

        // Books by category (top 5)
        $booksByCategory = BorrowRecord::where('user_id', $user->id)
            ->whereHas('bookCopy.book')
            ->with('bookCopy.book.category')
            ->get()
            ->pluck('bookCopy.book.category')
            ->filter()
            ->groupBy('name')
            ->map(fn ($items) => $items->count())
            ->sortDesc()
            ->take(5)
            ->toArray();

        // Monthly borrowing trend (last 6 months)
        $monthlyTrend = BorrowRecord::where('user_id', $user->id)
            ->where('borrowed_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn ($r) => $r->borrowed_at?->format('Y-m'))
            ->map(fn ($items) => $items->count())
            ->toArray();

        return $this->response->success([
            'overview' => [
                'total_borrowed' => $totalBorrowed,
                'active_loans' => $activeLoans,
                'completed_loans' => $completedLoans,
                'overdue_count' => $overdueCount,
                'total_fines' => $totalFines,
                'pending_fines' => $pendingFines,
                'digital_read_count' => $digitalReadCount,
                'digital_assets_downloaded' => $digitalAssetsDownloaded,
            ],
            'books_by_category' => $booksByCategory,
            'monthly_borrowing_trend' => $monthlyTrend,
        ]);
    }

    /**
     * Loan history report with optional date range.
     */
    public function loanHistory(): JsonResponse
    {
        $user = auth()->user();

        $loans = BorrowRecord::with(['bookCopy.book.authors', 'bookCopy.book.category'])
            ->where('user_id', $user->id)
            ->latest('borrowed_at')
            ->paginate(20);

        return $this->response->paginated($loans);
    }

    /**
     * Fine history report.
     */
    public function fineHistory(): JsonResponse
    {
        $user = auth()->user();

        $fines = Fine::with(['borrowRecord.bookCopy.book'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return $this->response->paginated($fines);
    }
}
