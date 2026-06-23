<?php

namespace App\Services;

use App\Models\DocumentVerification;
use App\Modules\Settings\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentService
{
    public function generateReportPdf(string $name, array $sections): \Barryvdh\DomPDF\PDF
    {
        $branding = $this->getBranding();
        $verification = $this->registerDocument($name, 'report');
        $qrCodeSvg = $this->generateQrSvg($verification->verification_url);

        $documentMeta = [
            'title' => $name,
            'type' => 'report',
            'author' => auth()->user()?->name ?? config('app.name'),
            'subject' => $name,
            'keywords' => 'library, report, '.strtolower($name),
            'generated_at' => $verification->generated_at->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()?->name ?? 'System',
            'document_id' => $verification->document_id,
            'verification_url' => $verification->verification_url,
            'qr_code_svg' => $qrCodeSvg,
        ];

        $pdf = Pdf::loadView('documents.report', compact('name', 'sections', 'branding', 'documentMeta'));
        $pdf->setPaper('A4', 'portrait');

        $dompdf = $pdf->getDomPDF();
        $dompdf->getCanvas()->get_cpdf()->addInfo('Title', $documentMeta['title']);
        $dompdf->getCanvas()->get_cpdf()->addInfo('Author', $documentMeta['author']);
        $dompdf->getCanvas()->get_cpdf()->addInfo('Subject', $documentMeta['subject']);
        $dompdf->getCanvas()->get_cpdf()->addInfo('Keywords', $documentMeta['keywords']);
        $dompdf->getCanvas()->get_cpdf()->addInfo('Creator', config('app.name').' Library System');

        return $pdf;
    }

    public function savePdf(\Barryvdh\DomPDF\PDF $pdf, string $filename): string
    {
        $path = 'documents/'.$filename;
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function getBranding(): array
    {
        $settings = app(SettingsService::class);
        $branding = $settings->getBrandingSettings();
        $display = $settings->getDisplaySettings();

        $logoPath = $branding['document_logo'] ?? '';
        $logoUrl = '';
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $fullPath = storage_path('app/public/'.$logoPath);
            if (file_exists($fullPath)) {
                $logoUrl = $fullPath;
            }
        }

        return [
            'logo_path' => $logoPath,
            'logo_url' => $logoUrl,
            'header_text' => $branding['document_header_text'] ?? config('app.name'),
            'footer_text' => $branding['document_footer_text'] ?? 'Official Library Document',
            'primary_color' => $branding['document_primary_color'] ?? '#1E4FA3',
            'show_verification_stamp' => (bool) ($branding['document_show_verification_stamp'] ?? true),
            'show_qr_code' => (bool) ($branding['document_show_qr_code'] ?? true),
            'watermark_text' => $branding['document_watermark_text'] ?? 'DRAFT',
            'footer_disclaimer' => $branding['document_footer_disclaimer'] ?? 'This document is electronically generated and is valid without a signature.',
            'institution_name' => $display['site_name'] ?? config('app.name'),
            'institution_address' => $display['library_address'] ?? '',
            'institution_phone' => $display['library_phone'] ?? '',
            'institution_email' => $display['library_email'] ?? '',
        ];
    }

    protected function registerDocument(string $title, string $type): DocumentVerification
    {
        return DocumentVerification::create([
            'document_id' => DocumentVerification::generateDocumentId(),
            'title' => $title,
            'type' => $type,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
            'metadata' => [
                'source' => url()->current(),
            ],
        ]);
    }

    protected function generateQrSvg(string $data): string
    {
        try {
            return QrCode::format('svg')
                ->size(120)
                ->errorCorrection('M')
                ->margin(1)
                ->generate($data);
        } catch (\Throwable $e) {
            Log::error('QR code generation failed', ['error' => $e->getMessage()]);

            return '';
        }
    }
}
