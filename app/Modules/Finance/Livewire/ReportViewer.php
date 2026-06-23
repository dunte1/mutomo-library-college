<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Finance\Models\Report;
use App\Modules\Finance\Services\ReportingService;
use App\Services\DownloadService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ReportViewer extends Component
{
    public string $selectedType = 'circulation_summary';
    public string $format = 'pdf';
    public array $params = [];
    public string $activeTab = 'generate';
    public ?Report $generatedReport = null;

    public function generate()
    {
        $this->validate([
            'format' => 'required|in:pdf,csv',
        ]);

        $report = app(ReportingService::class)->generateReport(
            $this->selectedType,
            $this->params,
            $this->format
        );

        $this->generatedReport = $report;

        session()->flash('success', 'Report generated successfully. You can download it below.');
    }

    public function download(int $reportId)
    {
        $report = Report::findOrFail($reportId);

        if (!$report->file_path || !Storage::disk('local')->exists($report->file_path)) {
            session()->flash('error', 'Report file not found.');
            return;
        }

        $extension = $report->file_type === 'csv' ? '.csv' : '.pdf';

        return app(DownloadService::class)->download(
            $report,
            Storage::disk('local')->path($report->file_path),
            $report->name . $extension,
            $report->name,
            'report',
            'generate-reports'
        );
    }

    public function render()
    {
        return view('finance::livewire.report-viewer', [
            'reportTypes' => Report::typeOptions(),
            'recentReports' => Report::latest()->take(10)->get(),
        ])->layout('layouts.app');
    }
}
