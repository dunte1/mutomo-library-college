<?php

namespace App\Jobs;

use App\Modules\Finance\Models\Report;
use App\Modules\Finance\Services\ReportingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function handle(): void
    {
        try {
            $service = app(ReportingService::class);
            $data = $service->generateReport($this->report);

            $documentService = app(\App\Services\DocumentService::class);

            if ($this->report->type === 'pdf') {
                $pdfPath = $documentService->generateReportPdf(
                    $this->report->name,
                    $data,
                    'financial'
                );
                $this->report->update([
                    'status' => 'completed',
                    'file_path' => $pdfPath,
                    'generated_at' => now(),
                ]);
            } else {
                $csvPath = $service->generateCsv($this->report, $data);
                $this->report->update([
                    'status' => 'completed',
                    'file_path' => $csvPath,
                    'generated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            $this->report->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            activity()
                ->performedOn($this->report)
                ->log("Report generation failed: {$e->getMessage()}");
        }
    }
}
