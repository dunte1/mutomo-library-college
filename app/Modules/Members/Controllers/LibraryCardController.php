<?php

namespace App\Modules\Members\Controllers;

use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Services\LibraryCardService;
use App\Modules\Settings\Services\SettingsService;
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

        if (! $card) {
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

    public function mobileView(Request $request)
    {
        $memberId = (int) $request->query('u');
        $expiresAt = (int) $request->query('e');
        $signature = (string) $request->query('s');

        $expected = hash_hmac('sha256', "{$memberId}.{$expiresAt}", config('app.key'));

        if ($memberId < 1 || ! hash_equals($expected, $signature)) {
            abort(403, 'Invalid link.');
        }

        if (now()->timestamp > $expiresAt) {
            abort(410, 'This link has expired. Please open the library card from the app again.');
        }

        $member = Member::findOrFail($memberId);
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $settingsService = app(SettingsService::class);

        return response()
            ->view('members::mobile.library-card', [
                'card' => $card,
                'member' => $member,
                'cardBranding' => $settingsService->getCardBrandingSettings(),
                'displaySettings' => $settingsService->getDisplaySettings(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
