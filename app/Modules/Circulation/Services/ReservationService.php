<?php

namespace App\Modules\Circulation\Services;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\Reservation;
use App\Modules\Notifications\Services\NotificationService;

class ReservationService
{
    public function placeHold(User $user, Book $book, ?string $notes = null): Reservation
    {
        $existing = Reservation::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', Reservation::STATUS_PENDING)
            ->first();

        if ($existing) {
            throw new \RuntimeException('You already have an active hold on this book.');
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => Reservation::STATUS_PENDING,
            'reserved_at' => now(),
            'expires_at' => now()->addDays(14),
            'notes' => $notes,
        ]);

        return $reservation;
    }

    public function cancelHold(int $reservationId, User $user): void
    {
        $reservation = Reservation::where('id', $reservationId)
            ->where('user_id', $user->id)
            ->where('status', Reservation::STATUS_PENDING)
            ->firstOrFail();

        $reservation->update([
            'status' => Reservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    public function fulfillHold(int $reservationId, BookCopy $copy): void
    {
        $reservation = Reservation::where('id', $reservationId)
            ->where('status', Reservation::STATUS_PENDING)
            ->firstOrFail();

        $reservation->update([
            'status' => Reservation::STATUS_FULFILLED,
            'fulfilled_at' => now(),
        ]);

        app(NotificationService::class)->sendHoldAvailable(
            $reservation->user,
            $reservation->book->title,
        );
    }

    public function expireOldHolds(): int
    {
        $expired = Reservation::where('status', Reservation::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->update([
                'status' => Reservation::STATUS_EXPIRED,
            ]);

        return $expired;
    }

    public function getUserReservations(User $user): iterable
    {
        return Reservation::with('book')
            ->where('user_id', $user->id)
            ->latest('reserved_at')
            ->get();
    }

    public function canPlaceHold(User $user, Book $book): bool
    {
        $existing = Reservation::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', Reservation::STATUS_PENDING)
            ->exists();

        if ($existing) {
            return false;
        }

        $availableCopies = $book->availableCopies()->count();
        $pendingReservations = Reservation::where('book_id', $book->id)
            ->where('status', Reservation::STATUS_PENDING)
            ->count();

        if ($availableCopies > $pendingReservations) {
            return false;
        }

        return true;
    }
}
