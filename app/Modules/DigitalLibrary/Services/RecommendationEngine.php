<?php

namespace App\Modules\DigitalLibrary\Services;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\ReadingHistory;
use App\Modules\DigitalLibrary\Models\Recommendation;

class RecommendationEngine
{
    public function generateForUser(User $user, int $limit = 10): array
    {
        $recommendations = [];

        if ($user->hasRole('student') || $user->hasRole('lecturer')) {
            $fromBorrowHistory = $this->fromBorrowHistory($user, $limit);
            $recommendations = array_merge($recommendations, $fromBorrowHistory);
        }

        $fromReadingHistory = $this->fromReadingHistory($user, $limit);
        $recommendations = array_merge($recommendations, $fromReadingHistory);

        $popular = $this->popularInDepartment($user, $limit);
        $recommendations = array_merge($recommendations, $popular);

        $newArrivals = $this->newArrivals($limit);
        $recommendations = array_merge($recommendations, $newArrivals);

        $unique = collect($recommendations)
            ->unique(fn ($r) => ($r['book_id'] ?? 0).'-'.($r['digital_asset_id'] ?? 0))
            ->take($limit)
            ->values()
            ->toArray();

        foreach ($unique as &$rec) {
            Recommendation::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $rec['type'],
                    'book_id' => $rec['book_id'] ?? null,
                    'digital_asset_id' => $rec['digital_asset_id'] ?? null,
                ],
                [
                    'score' => $rec['score'],
                    'reason' => $rec['reason'],
                    'expires_at' => now()->addDays(7),
                ]
            );
        }

        return $unique;
    }

    public function fromBorrowHistory(User $user, int $limit = 5): array
    {
        $categoryIds = BorrowRecord::where('user_id', $user->id)
            ->whereHas('bookCopy.book')
            ->with('bookCopy.book')
            ->get()
            ->flatMap(fn ($r) => $r->bookCopy?->book?->category_id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($categoryIds)) {
            return [];
        }

        $excludedBookIds = BorrowRecord::where('user_id', $user->id)
            ->whereHas('bookCopy')
            ->with('bookCopy.book')
            ->get()
            ->pluck('bookCopy.book.id')
            ->filter()
            ->unique()
            ->toArray();

        $books = Book::whereIn('category_id', $categoryIds)
            ->when(! empty($excludedBookIds), fn ($q) => $q->whereNotIn('id', $excludedBookIds))
            ->withCount('copies')
            ->orderByDesc('copies_count')
            ->limit($limit)
            ->get();

        return $books->map(fn ($book) => [
            'book_id' => $book->id,
            'type' => 'based_on_history',
            'score' => 0.95,
            'reason' => 'Based on your borrowing history',
        ])->toArray();
    }

    public function fromReadingHistory(User $user, int $limit = 5): array
    {
        $categoryIds = ReadingHistory::where('user_id', $user->id)
            ->whereHas('digitalAsset.category')
            ->with('digitalAsset.category')
            ->get()
            ->flatMap(fn ($h) => $h->digitalAsset?->category_id)
            ->unique()
            ->toArray();

        if (empty($categoryIds)) {
            return [];
        }

        $excludedIds = ReadingHistory::where('user_id', $user->id)
            ->pluck('digital_asset_id')
            ->toArray();

        $assets = DigitalAsset::whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $excludedIds)
            ->active()
            ->limit($limit)
            ->get();

        return $assets->map(fn ($asset) => [
            'digital_asset_id' => $asset->id,
            'type' => 'personalized',
            'score' => 0.9,
            'reason' => 'Recommended based on your reading history',
        ])->toArray();
    }

    public function popularInDepartment(User $user, int $limit = 5): array
    {
        $departmentId = $user->department_id;
        if (! $departmentId) {
            return [];
        }

        $bookIds = BorrowRecord::whereHas('user', fn ($q) => $q->where('department_id', $departmentId))
            ->selectRaw('book_copy_id, COUNT(*) as borrow_count')
            ->groupBy('book_copy_id')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->pluck('book_copy_id')
            ->toArray();

        if (empty($bookIds)) {
            return [];
        }

        $books = Book::whereHas('copies', fn ($q) => $q->whereIn('id', $bookIds))
            ->limit($limit)
            ->get();

        return $books->map(fn ($book) => [
            'book_id' => $book->id,
            'type' => 'popular',
            'score' => 0.85,
            'reason' => 'Popular in your department',
        ])->toArray();
    }

    public function newArrivals(int $limit = 5): array
    {
        $books = Book::orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $books->map(fn ($book) => [
            'book_id' => $book->id,
            'type' => 'new_arrival',
            'score' => 0.75,
            'reason' => 'New arrival in the library',
        ])->toArray();
    }

    public function similarBooks(Book $book, int $limit = 6): array
    {
        $categoryId = $book->category_id;

        if (! $categoryId) {
            return [];
        }

        $similar = Book::where('id', '!=', $book->id)
            ->where('category_id', $categoryId)
            ->limit($limit)
            ->get();

        return $similar->map(fn ($b) => [
            'book_id' => $b->id,
            'type' => 'similar_book',
            'score' => 0.8,
            'reason' => 'Shares category with '.$book->title,
        ])->toArray();
    }

    public function predictiveOverdueAlert(User $user): ?string
    {
        $hasOverduePattern = BorrowRecord::where('user_id', $user->id)
            ->where('status', BorrowRecord::STATUS_OVERDUE)
            ->count() >= 2;

        if ($hasOverduePattern) {
            return 'You have had multiple overdue items. Consider setting up reminders for due dates.';
        }

        $activeCount = BorrowRecord::where('user_id', $user->id)
            ->where('status', BorrowRecord::STATUS_ACTIVE)
            ->count();

        $borrowLimit = $user->getBorrowLimit();

        if ($activeCount >= $borrowLimit) {
            return "You've reached your borrow limit ({$borrowLimit}). Return an item before borrowing more.";
        }

        return null;
    }
}
