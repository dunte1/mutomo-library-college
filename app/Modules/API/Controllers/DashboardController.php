<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\NotificationResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Communication\Models\Event;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\Notifications\Models\InAppNotification;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        // Active loans
        $activeLoans = BorrowRecord::with(['bookCopy.book.authors', 'bookCopy.book.category'])
            ->where('user_id', $user->id)
            ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'book' => $r->bookCopy?->book ? [
                    'id' => $r->bookCopy->book->id,
                    'title' => $r->bookCopy->book->title,
                    'cover_image' => $r->bookCopy->book->cover_image
                        ? url('storage/'.$r->bookCopy->book->cover_image) : null,
                ] : null,
                'barcode' => $r->bookCopy?->barcode,
                'borrowed_at' => $r->borrowed_at?->toIso8601String(),
                'due_at' => $r->due_at?->toIso8601String(),
                'status' => $r->status,
                'days_remaining' => $r->due_at ? (int) max(0, now()->diffInDays($r->due_at, false)) : null,
            ]);

        // Due soon (next 3 days)
        $dueSoon = BorrowRecord::with(['bookCopy.book'])
            ->where('user_id', $user->id)
            ->where('status', BorrowRecord::STATUS_ACTIVE)
            ->where('due_at', '<=', now()->addDays(3))
            ->where('due_at', '>', now())
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'book_title' => $r->bookCopy?->book?->title,
                'due_at' => $r->due_at?->toIso8601String(),
                'days_remaining' => (int) now()->diffInDays($r->due_at, false),
            ]);

        // Latest notifications
        $latestNotifications = InAppNotification::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        // Featured books
        $featuredBooks = Book::active()
            ->featured()
            ->with(['authors', 'category'])
            ->take(6)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'cover_image' => $b->cover_image ? url('storage/'.$b->cover_image) : null,
                'authors' => $b->authors->map(fn ($a) => ['name' => $a->name]),
                'available_copies' => $b->available_copies,
                'average_rating' => $b->average_rating,
            ]);

        // Recent digital assets
        $recentAssets = DigitalAsset::active()
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'file_type' => $a->file_type,
                'cover_image' => $a->cover_image ? url('storage/'.$a->cover_image) : null,
            ]);

        // Upcoming events
        $upcomingEvents = Event::where('status', 'published')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(3)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'location' => $e->location,
                'start_date' => $e->start_date?->toIso8601String(),
                'type' => $e->type,
            ]);

        return $this->response->success([
            'user' => [
                'name' => $user->name,
                'avatar' => $user->avatar ? url('storage/'.$user->avatar) : null,
            ],
            'stats' => [
                'total_books' => Book::count(),
                'active_borrows' => BorrowRecord::where('user_id', $user->id)
                    ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])->count(),
                'active_loans' => BorrowRecord::where('user_id', $user->id)
                    ->whereIn('status', [BorrowRecord::STATUS_ACTIVE, BorrowRecord::STATUS_OVERDUE])->count(),
                'overdue_borrows' => BorrowRecord::where('user_id', $user->id)
                    ->overdue()->count(),
                'overdue_loans' => BorrowRecord::where('user_id', $user->id)
                    ->overdue()->count(),
                'total_fines' => (float) $user->fines()->sum('amount'),
                'pending_fines' => $user->fines()->pending()->count(),
                'pending_fines_total' => (float) $user->fines()->pending()->sum('amount'),
                'available_books' => Book::where('available_copies', '>', 0)->count(),
                'digital_assets' => DigitalAsset::active()->count(),
                'active_reservations' => $user->reservations()->pending()->count(),
                'pending_reservations' => $user->reservations()->pending()->count(),
                'unread_notifications' => InAppNotification::where('user_id', $user->id)->unread()->count(),
                'borrow_limit' => $user->getBorrowLimit(),
            ],
            'active_loans' => $activeLoans,
            'due_soon' => $dueSoon,
            'latest_notifications' => NotificationResource::collection($latestNotifications),
            'featured_books' => $featuredBooks,
            'recent_digital_assets' => $recentAssets,
            'upcoming_events' => $upcomingEvents,
        ]);
    }
}
