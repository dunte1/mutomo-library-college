<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\Reservation;
use App\Modules\Circulation\Services\ReservationService;
use Livewire\Component;

class PatronReservation extends Component
{
    public string $search = '';

    public ?int $selectedBookId = null;

    public function placeHold(int $bookId): void
    {
        try {
            $user = auth()->user();
            $book = Book::findOrFail($bookId);
            app(ReservationService::class)->placeHold($user, $book);
            $this->dispatch('notify', type: 'success', message: "Hold placed on '{$book->title}'.");
            $this->selectedBookId = null;
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function cancelHold(int $reservationId): void
    {
        try {
            app(ReservationService::class)->cancelHold($reservationId, auth()->user());
            $this->dispatch('notify', type: 'success', message: 'Reservation cancelled.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $reservations = Reservation::with('book')
            ->where('user_id', $user->id)
            ->latest('reserved_at')
            ->get();

        $books = collect();
        if ($this->search) {
            $books = Book::where('title', 'like', "%{$this->search}%")
                ->orWhere('isbn', 'like', "%{$this->search}%")
                ->limit(10)
                ->get();
        }

        return view('circulation::livewire.patron-reservation', [
            'reservations' => $reservations,
            'books' => $books,
        ]);
    }
}
