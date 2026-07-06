<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ReviewController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(int $bookId): JsonResponse
    {
        $reviews = BookReview::with('user')
            ->approved()
            ->where('book_id', $bookId)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($r) => $this->format($r));

        $stats = [
            'average_rating' => (float) BookReview::approved()->where('book_id', $bookId)->avg('rating') ?? 0,
            'total_reviews' => BookReview::approved()->where('book_id', $bookId)->count(),
            'distribution' => collect(range(1, 5))->mapWithKeys(fn ($r) => [
                $r => BookReview::approved()->where('book_id', $bookId)->byRating($r)->count(),
            ]),
        ];

        return $this->response->success($reviews, extra: ['meta' => $stats]);
    }

    public function store(): JsonResponse
    {
        $data = request()->validate([
            'book_id' => 'required|integer|exists:books,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $existing = BookReview::where('book_id', $data['book_id'])
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $data['rating'],
                'review' => $data['review'] ?? $existing->review,
                'is_approved' => false,
            ]);
            return $this->response->success($this->format($existing->fresh()), 'Review updated and pending approval.');
        }

        $review = BookReview::create([
            'book_id' => $data['book_id'],
            'user_id' => auth()->id(),
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
            'is_approved' => false,
        ]);

        return $this->response->created($this->format($review), 'Review submitted for approval.');
    }

    public function show(int $id): JsonResponse
    {
        $review = BookReview::with('user')->findOrFail($id);
        return $this->response->success($this->format($review));
    }

    public function my(): JsonResponse
    {
        $reviews = BookReview::with('book')
            ->where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'book_id' => $r->book_id,
                'book_title' => $r->book?->title,
                'book_cover' => $r->book?->cover_image ? url('storage/'.$r->book->cover_image) : null,
                'rating' => $r->rating,
                'review' => $r->review,
                'is_approved' => $r->is_approved,
                'created_at' => $r->created_at?->toIso8601String(),
            ]);

        return $this->response->success($reviews);
    }

    protected function format(BookReview $review): array
    {
        return [
            'id' => $review->id,
            'book_id' => $review->book_id,
            'user_id' => $review->user_id,
            'user_name' => $review->user?->name,
            'user_avatar' => $review->user?->avatar ? url('storage/'.$review->user->avatar) : null,
            'rating' => $review->rating,
            'review' => $review->review,
            'is_approved' => $review->is_approved,
            'created_at' => $review->created_at?->toIso8601String(),
        ];
    }
}
