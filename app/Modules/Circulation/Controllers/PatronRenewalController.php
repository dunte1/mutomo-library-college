<?php

namespace App\Modules\Circulation\Controllers;

use App\Modules\Circulation\Services\BorrowingService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PatronRenewalController extends Controller
{
    public function renew(Request $request, int $borrowId)
    {
        try {
            $record = \App\Modules\Circulation\Models\BorrowRecord::findOrFail($borrowId);

            if ($record->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            app(BorrowingService::class)->renewBook($borrowId);
            app(\App\Modules\Notifications\Services\NotificationService::class)->send(
                $request->user(),
                'return',
                'Book Renewed',
                "Your book has been renewed successfully.",
                'archive',
                route('dashboard'),
            );

            return response()->json(['success' => true, 'message' => 'Book renewed successfully.']);
        } catch (\RuntimeException $e) {
            \Illuminate\Support\Facades\Log::warning('Patron renewal failed', ['error' => $e->getMessage(), 'borrow_id' => $borrowId]);
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Patron renewal error', ['error' => $e->getMessage(), 'borrow_id' => $borrowId]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
