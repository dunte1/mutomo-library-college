<?php

namespace App\Modules\Members\Services;

use App\Models\DownloadLog;
use App\Models\User;
use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Models\Member;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LibraryCardService
{
    /**
     * Generate a unique library card number in OLLMCHS-{YEAR}-{SEQUENCE} format.
     */
    public function generateCardNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "OLLMCHS-{$year}-";

        $lastCard = LibraryCard::where('card_number', 'like', "{$prefix}%")
            ->orderBy('card_number', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastCard) {
            $parts = explode('-', $lastCard->card_number);
            $sequence = (int) end($parts) + 1;
        } else {
            $sequence = 1;
        }

        $cardNumber = $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);

        while (LibraryCard::where('card_number', $cardNumber)->exists()) {
            $sequence++;
            $cardNumber = $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);
        }

        return $cardNumber;
    }

    /**
     * Generate QR code SVG data for a library card.
     */
    public function generateQrCode(LibraryCard $card): string
    {
        $verificationUrl = route('verify.card', ['cardNumber' => $card->card_number]);

        try {
            return QrCode::format('svg')
                ->size(200)
                ->errorCorrection('M')
                ->margin(1)
                ->generate($verificationUrl);
        } catch (\Throwable $e) {
            report($e);
            return '';
        }
    }

    /**
     * Generate a barcode string for the card.
     */
    public function generateBarcode(LibraryCard $card): string
    {
        return $card->card_number;
    }

    /**
     * Issue a new library card for a member.
     */
    public function issueCard(Member $member, User $issuedBy, ?string $passportPhoto = null): LibraryCard
    {
        return DB::transaction(function () use ($member, $issuedBy, $passportPhoto) {
            // Mark any existing active cards as replaced
            LibraryCard::where('member_id', $member->id)
                ->where('status', 'active')
                ->update(['status' => 'replaced', 'replaced_by' => $issuedBy->id]);

            $cardNumber = $this->generateCardNumber();

            $card = LibraryCard::create([
                'member_id' => $member->id,
                'card_number' => $cardNumber,
                'status' => 'active',
                'issued_at' => now(),
                'expires_at' => now()->addYear(),
                'issued_by' => $issuedBy->id,
                'passport_photo' => $passportPhoto,
            ]);

            // Generate QR code
            $card->update([
                'qr_code' => $this->generateQrCode($card),
                'barcode' => $this->generateBarcode($card),
            ]);

            activity()
                ->performedOn($card)
                ->causedBy($issuedBy)
                ->withProperties(['member_id' => $member->id, 'card_number' => $cardNumber])
                ->log("Library card issued: {$cardNumber} for {$member->full_name}");

            return $card->fresh();
        });
    }

    /**
     * Reissue a card (mark old as replaced, issue new one).
     */
    public function reissueCard(LibraryCard $oldCard, User $issuedBy, ?string $passportPhoto = null): LibraryCard
    {
        return DB::transaction(function () use ($oldCard, $issuedBy, $passportPhoto) {
            $oldCard->markAsReplaced();

            $member = $oldCard->member;
            $cardNumber = $this->generateCardNumber();

            $card = LibraryCard::create([
                'member_id' => $member->id,
                'card_number' => $cardNumber,
                'status' => 'active',
                'issued_at' => now(),
                'expires_at' => now()->addYear(),
                'issued_by' => $issuedBy->id,
                'replaced_by' => $issuedBy->id,
                'passport_photo' => $passportPhoto ?? $oldCard->passport_photo,
            ]);

            $card->update([
                'qr_code' => $this->generateQrCode($card),
                'barcode' => $this->generateBarcode($card),
            ]);

            activity()
                ->performedOn($card)
                ->causedBy($issuedBy)
                ->withProperties([
                    'member_id' => $member->id,
                    'card_number' => $cardNumber,
                    'replaced_card' => $oldCard->card_number,
                ])
                ->log("Library card reissued: {$cardNumber} (replaced {$oldCard->card_number})");

            return $card->fresh();
        });
    }

    /**
     * Generate PDF for a library card.
     */
    public function generateCardPdf(LibraryCard $card, User $user): \Barryvdh\DomPDF\PDF
    {
        $member = $card->member;
        $photoUrl = null;

        if ($card->passport_photo) {
            $fullPath = storage_path('app/public/' . $card->passport_photo);
            if (file_exists($fullPath)) {
                $photoUrl = $fullPath;
            }
        } elseif ($member->photo) {
            $fullPath = storage_path('app/public/' . $member->photo);
            if (file_exists($fullPath)) {
                $photoUrl = $fullPath;
            }
        }

        $qrCodeSvg = $card->qr_code;

        $pdf = Pdf::loadView('members::pdf.library-card', compact('card', 'member', 'photoUrl', 'qrCodeSvg'));
        $pdf->setPaper([0, 0, 340, 540], 'portrait');

        $dompdf = $pdf->getDomPDF();
        $dompdf->getCanvas()->get_cpdf()->addInfo('Title', "Library Card - {$member->full_name}");
        $dompdf->getCanvas()->get_cpdf()->addInfo('Author', config('app.name'));
        $dompdf->getCanvas()->get_cpdf()->addInfo('Subject', "Library Card {$card->card_number}");
        $dompdf->getCanvas()->get_cpdf()->addInfo('Keywords', 'library, card, ' . $card->card_number);

        // Log download
        DownloadLog::create([
            'user_id' => $user->id,
            'downloadable_type' => LibraryCard::class,
            'downloadable_id' => $card->id,
            'type' => 'library_card',
            'title' => "Library Card - {$member->full_name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        activity()
            ->performedOn($card)
            ->causedBy($user)
            ->log("Library card PDF downloaded: {$card->card_number}");

        return $pdf;
    }

    /**
     * Get card statistics for analytics.
     */
    public function getCardStats(): array
    {
        return [
            'total' => LibraryCard::count(),
            'active' => LibraryCard::where('status', 'active')->count(),
            'lost' => LibraryCard::where('status', 'lost')->count(),
            'replaced' => LibraryCard::where('status', 'replaced')->count(),
            'expired' => LibraryCard::where('status', 'expired')->count(),
            'issued_this_month' => LibraryCard::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Auto-issue card when a member is created.
     */
    public function autoIssueCard(Member $member): void
    {
        try {
            $user = $member->registeredBy ?? User::where('is_active', true)->first();
            if (!$user) {
                $user = User::first();
            }
            if ($user) {
                $this->issueCard($member, $user);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
