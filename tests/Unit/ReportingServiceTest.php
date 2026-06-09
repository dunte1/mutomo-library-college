<?php

namespace Tests\Unit;

use App\Modules\Finance\Models\Report;
use App\Modules\Finance\Services\ReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReportingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->service = app(ReportingService::class);
    }

    public function test_can_generate_pdf_report(): void
    {
        $report = $this->service->generateReport('catalog_inventory', [], 'pdf');

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('pdf', $report->file_type);
        $this->assertEquals('completed', $report->status);
        $this->assertNotNull($report->file_path);
    }

    public function test_can_generate_csv_report(): void
    {
        $report = $this->service->generateReport('catalog_inventory', [], 'csv');

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('csv', $report->file_type);
        $this->assertEquals('completed', $report->status);
        $this->assertNotNull($report->file_path);
    }

    public function test_csv_report_contains_data(): void
    {
        $report = $this->service->generateReport('catalog_inventory', [], 'csv');
        $content = \Illuminate\Support\Facades\Storage::disk('local')->get($report->file_path);

        $this->assertStringContainsString('total_books', $content);
    }

    public function test_each_report_type_generates(): void
    {
        $types = ['circulation_summary', 'overdue_report', 'fine_report', 'popular_books', 'catalog_inventory'];

        foreach ($types as $type) {
            $report = $this->service->generateReport($type, [], 'csv');
            $this->assertEquals('completed', $report->status, "Failed for type: {$type}");
            $report->delete();
        }
    }

    public function test_data_to_sections_formats_correctly(): void
    {
        $ref = new \ReflectionMethod($this->service, 'dataToSections');
        $ref->setAccessible(true);

        $data = [
            'summary' => [
                'total' => 10,
                'items' => [['name' => 'A', 'count' => 1], ['name' => 'B', 'count' => 2]],
            ],
        ];

        $sections = $ref->invoke($this->service, $data);

        $this->assertCount(2, $sections);
        $this->assertEquals('Summary', $sections[0]['label']);
        $this->assertEquals(['Item', 'Value'], $sections[0]['headers']);
    }
}
