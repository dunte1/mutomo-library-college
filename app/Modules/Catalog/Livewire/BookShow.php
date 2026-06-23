<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookReview;
use App\Modules\Catalog\Services\BookService;
use App\Modules\Circulation\Models\Reservation;
use App\Modules\Circulation\Services\ReservationService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BookShow extends Component
{
    public Book $book;

    public ?string $reviewText = null;

    public int $reviewRating = 5;

    public bool $canReserve = false;

    public ?string $reserveError = null;

    public ?string $reserveSuccess = null;

    public function mount(int $id): void
    {
        $this->book = app(BookService::class)->find($id);
        $this->checkReservationAvailability();
    }

    public function placeHold(): void
    {
        $this->reserveError = null;
        $this->reserveSuccess = null;

        try {
            app(ReservationService::class)->placeHold(auth()->user(), $this->book);
            $this->reserveSuccess = 'Hold placed successfully. You will be notified when a copy becomes available.';
            $this->canReserve = false;
        } catch (\RuntimeException $e) {
            $this->reserveError = $e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Place hold failed', ['error' => $e->getMessage()]);
            $this->reserveError = 'An unexpected error occurred.';
        }
    }

    public function submitReview(): void
    {
        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewText' => 'nullable|string|max:2000',
        ]);

        $existing = BookReview::where('book_id', $this->book->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $this->reviewRating,
                'review' => $this->reviewText,
            ]);
        } else {
            BookReview::create([
                'book_id' => $this->book->id,
                'user_id' => auth()->id(),
                'rating' => $this->reviewRating,
                'review' => $this->reviewText,
            ]);
        }

        $this->book->load('approvedReviews.user');
        $this->dispatch('notify', message: 'Review submitted successfully.', type: 'success');
    }

    protected function checkReservationAvailability(): void
    {
        try {
            $this->canReserve = app(ReservationService::class)->canPlaceHold(auth()->user(), $this->book);
        } catch (\Throwable $e) {
            $this->canReserve = false;
        }
    }

    public function render()
    {
        return view('catalog::livewire.book-show', [
            'copies' => $this->book->copies()->orderBy('created_at', 'desc')->get(),
            'reviews' => $this->book->approvedReviews()->with('user')->latest()->get(),
            'userReview' => BookReview::where('book_id', $this->book->id)
                ->where('user_id', auth()->id())->first(),
            'hasActiveReservation' => Reservation::where('user_id', auth()->id())
                ->where('book_id', $this->book->id)
                ->where('status', Reservation::STATUS_PENDING)
                ->exists(),
        ]);
    }
}
