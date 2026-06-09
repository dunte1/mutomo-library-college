<?php

namespace App\Modules\Members\Controllers;

use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Services\LibraryCardService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LibraryCardController extends Controller
{
    public function download(int $memberId, LibraryCardService $cardService)
    {
        $card = LibraryCard::where('member_id', $memberId)
            ->where('status', 'active')
            ->latest()
            ->firstOrFail();

        // Authorization handled by route middleware (permission:view-library-cards)

        $pdf = $cardService->generateCardPdf($card, auth()->user());
        $filename = "library-card-{$card->card_number}.pdf";

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function verify(string $cardNumber)
    {
        $card = LibraryCard::where('card_number', $cardNumber)->first();

        if (!$card) {
            return view('members::card-verify', [
                'valid' => false,
                'message' => 'Invalid card number. No library card found.',
            ]);
        }

        $member = $card->member;

        return view('members::card-verify', [
            'valid' => true,
            'card' => $card,
            'member' => $member,
            'message' => 'This library card is valid and active.',
        ]);
    }
}
