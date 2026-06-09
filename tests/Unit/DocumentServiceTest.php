<?php

namespace Tests\Unit;

use App\Models\DocumentVerification;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_pdf_with_branding(): void
    {
        $service = app(DocumentService::class);
        $pdf = $service->generateReportPdf('Test Report', [
            ['label' => 'Section 1', 'headers' => ['Name', 'Value'], 'rows' => [['Item 1', '100']]],
        ]);

        $this->assertStringContainsString('%PDF', $pdf->output());
    }

    public function test_registers_document_verification(): void
    {
        $service = app(DocumentService::class);
        $ref = new \ReflectionMethod($service, 'registerDocument');
        $ref->setAccessible(true);
        $doc = $ref->invoke($service, 'Test Doc', 'report');

        $this->assertInstanceOf(DocumentVerification::class, $doc);
        $this->assertEquals('Test Doc', $doc->title);
        $this->assertEquals('report', $doc->type);
        $this->assertNotNull($doc->document_id);
        $this->assertStringStartsWith('DOC-', $doc->document_id);
    }

    public function test_generates_qr_svg(): void
    {
        $service = app(DocumentService::class);
        $ref = new \ReflectionMethod($service, 'generateQrSvg');
        $ref->setAccessible(true);
        $svg = $ref->invoke($service, 'https://example.com/verify/ABC');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('xmlns="http://www.w3.org/2000/svg"', $svg);
    }

    public function test_get_branding_returns_expected_keys(): void
    {
        $service = app(DocumentService::class);
        $branding = $service->getBranding();

        $expectedKeys = [
            'logo_path', 'logo_url', 'header_text', 'footer_text',
            'primary_color', 'show_verification_stamp', 'show_qr_code',
            'watermark_text', 'footer_disclaimer', 'institution_name',
            'institution_address', 'institution_phone', 'institution_email',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $branding);
        }
    }
}
