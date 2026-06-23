<?php

namespace App\Modules\Circulation\Controllers;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Services\BorrowingService;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class PatronRenewalController extends Controller
{
    public function renew(Request $request, int $borrowId)
    {
        try {
            $record = BorrowRecord::findOrFail($borrowId);

            if ($record->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            app(BorrowingService::class)->renewBook($borrowId);
            app(NotificationService::class)->send(
                $request->user(),
                'return',
                'Book Renewed',
                'Your book has been renewed successfully.',
                'archive',
                route('dashboard'),
            );

            return response()->json(['success' => true, 'message' => 'Book renewed successfully.']);
        } catch (\RuntimeException $e) {
            Log::warning('Patron renewal failed', ['error' => $e->getMessage(), 'borrow_id' => $borrowId]);

            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Patron renewal error', ['error' => $e->getMessage(), 'borrow_id' => $borrowId]);

            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
