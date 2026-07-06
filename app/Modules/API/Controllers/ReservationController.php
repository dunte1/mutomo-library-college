<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\ReservationResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\Reservation;
use App\Modules\Circulation\Services\ReservationService;
use Illuminate\Routing\Controller;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService,
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $reservations = Reservation::with('book.authors')
            ->where('user_id', auth()->id())
            ->latest('reserved_at')
            ->get();

        return $this->response->success(ReservationResource::collection($reservations));
    }

    public function store(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $book = Book::findOrFail($data['book_id']);

        if (! $this->reservationService->canPlaceHold(auth()->user(), $book)) {
            return $this->response->error('This book is currently available for borrowing, or you already have an active hold.', 422);
        }

        try {
            $reservation = $this->reservationService->placeHold(
                auth()->user(),
                $book,
                $data['notes'] ?? null,
            );

            return $this->response->success(
                new ReservationResource($reservation->load('book.authors')),
                'Book reserved successfully.',
                201
            );
        } catch (\RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->reservationService->cancelHold($id, auth()->user());

            return $this->response->success(null, 'Reservation cancelled.');
        } catch (\RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }
    }
}
