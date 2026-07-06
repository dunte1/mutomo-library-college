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
        $member = Member::where('user_id', auth()->id())->firstOrFail();
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
        $member = Member::where('user_id', auth()->id())->firstOrFail();
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->firstOrFail();

        $verificationUrl = route('verify.document', ['id' => $card->card_number]);

        return $this->response->success([
            'qr_code_svg' => $card->qr_code,
            'card_number' => $card->card_number,
            'verification_url' => $verificationUrl,
        ]);
    }

    public function pdf(): \Illuminate\Http\Response
    {
        $member = Member::where('user_id', auth()->id())->firstOrFail();
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->firstOrFail();

        $pdf = $this->libraryCardService->generateCardPdf($card, auth()->user());

        return $pdf->download("library-card-{$card->card_number}.pdf");
    }

    public function barcode(): \Illuminate\Http\JsonResponse
    {
        $member = Member::where('user_id', auth()->id())->firstOrFail();
        $card = LibraryCard::where('member_id', $member->id)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->response->success([
            'barcode' => $card->barcode,
            'card_number' => $card->card_number,
        ]);
    }
}
