<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\LibraryCardResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class LibraryCardController extends Controller
{
    public function __construct(
        protected LibraryCardService $libraryCardService,
        protected ApiResponseService $response,
    ) {}

    public function show(): \Illuminate\Http\JsonResponse
    {
        $member = Member::where('user_id', auth()->id())->first();
        if (! $member) {
            return $this->response->success(null, 'No library membership found. Please register as a member.');
        }
        $card = LibraryCard::with('member.department')
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->first();

        if (! $card) {
            return $this->response->notFound('No active library card found. Please visit the library to get your card issued.');
        }

        return $this->response->success(new LibraryCardResource($card));
    }

    public function qrCode(): \Illuminate\Http\JsonResponse
    {
        $member = Member::where('user_id', auth()->id())->first();
        if (! $member) {
            return $this->response->notFound('No library membership found.');
        }
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->first();

        if (! $card) {
            return $this->response->notFound('No active library card found.');
        }

        $verificationUrl = route('verify.document', ['id' => $card->card_number]);

        return $this->response->success([
            'qr_code_svg' => $card->qr_code,
            'card_number' => $card->card_number,
            'verification_url' => $verificationUrl,
        ]);
    }

    public function pdf(): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        $member = Member::where('user_id', auth()->id())->first();
        if (! $member) {
            return response()->json(['message' => 'No library membership found.'], 404);
        }
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->first();

        if (! $card) {
            return response()->json(['message' => 'No active library card found.'], 404);
        }

        $pdf = $this->libraryCardService->generateCardPdf($card, auth()->user());

        return $pdf->download("library-card-{$card->card_number}.pdf");
    }

    public function barcode(): \Illuminate\Http\JsonResponse
    {
        $member = Member::where('user_id', auth()->id())->first();
        if (! $member) {
            return $this->response->notFound('No library membership found.');
        }
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->first();

        if (! $card) {
            return $this->response->notFound('No active library card found.');
        }

        return $this->response->success([
            'barcode' => $card->barcode,
            'card_number' => $card->card_number,
        ]);
    }

    public function mobileUrl(): \Illuminate\Http\JsonResponse
    {
        $member = Member::where('user_id', auth()->id())->first();
        if (! $member) {
            return $this->response->notFound('No library membership found. Please register as a member.');
        }
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $card) {
            return $this->response->notFound('No active library card found. Please visit the library to get your card issued.');
        }

        $expiresAt = now()->addMinutes(15)->timestamp;
        $signature = hash_hmac('sha256', "{$member->id}.{$expiresAt}", config('app.key'));

        return $this->response->success([
            'url' => route('mobile.library-card', [
                'u' => $member->id,
                'e' => $expiresAt,
                's' => $signature,
            ]),
        ]);
    }
}
