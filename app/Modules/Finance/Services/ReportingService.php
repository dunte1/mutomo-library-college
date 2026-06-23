<?php

namespace App\Modules\Finance\Services;

use App\Models\User;
use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Finance\Models\Report;
use App\Modules\Finance\Models\Transaction;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Storage;

class ReportingService
{
    public function generateReport(string $type, array $parameters = [], string $format = 'pdf'): Report
    {
        $data = match ($type) {
            'circulation_summary' => $this->circulationSummary($parameters),
            'overdue_report' => $this->overdueReport($parameters),
            'fine_report' => $this->fineReport($parameters),
            'popular_books' => $this->popularBooks($parameters),
            'member_activity' => $this->memberActivity($parameters),
            'catalog_inventory' => $this->catalogInventory(),
            'financial_summary' => $this->financialSummary($parameters),
            'daily_transactions' => $this->dailyTransactions($parameters),
            default => [],
        };

        $sections = $this->dataToSections($data);
        $name = Report::typeOptions()[$type] ?? $type;

        if ($format === 'csv') {
            $filename = 'reports/'.$type.'_'.now()->format('Ymd_His').'.csv';
            $fileType = 'csv';
            $csvContent = $this->generateCsv($sections);
            Storage::disk('local')->put($filename, $csvContent);
        } else {
            $documentService = app(DocumentService::class);
            $pdf = $documentService->generateReportPdf($name, $sections);
            $filename = 'reports/'.$type.'_'.now()->format('Ymd_His').'.pdf';
            $fileType = 'pdf';
            Storage::disk('local')->put($filename, $pdf->output());
        }

        $report = Report::create([
            'name' => $name,
            'type' => $type,
            'parameters' => $parameters,
            'file_path' => $filename,
            'file_type' => $fileType,
            'status' => 'completed',
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);

        activity()
            ->performedOn($report)
            ->causedBy(auth()->user())
            ->log("Generated report: {$report->name}");

        return $report;
    }

    protected function generateCsv(array $sections): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($sections as $section) {
            fputcsv($output, ['--- '.$section['label'].' ---']);
            if (! empty($section['headers'])) {
                fputcsv($output, $section['headers']);
            }
            foreach ($section['rows'] as $row) {
                fputcsv($output, $row);
            }
            fputcsv($output, []);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    protected function dataToSections(array $data): array
    {
        $sections = [];

        foreach ($data as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));

            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                $headers = array_keys($value[0]);
                $rows = [];
                foreach ($value as $item) {
                    $rows[] = array_map(fn ($v) => is_array($v) ? json_encode($v) : $v, $item);
                }
                $sections[] = ['label' => $label, 'headers' => $headers, 'rows' => $rows];
            } elseif (is_array($value)) {
                $flat = [];
                foreach ($value as $k => $v) {
                    $flat[] = [ucwords(str_replace('_', ' ', $k)), is_array($v) ? json_encode($v) : $v];
                }
                $sections[] = ['label' => $label, 'headers' => ['Item', 'Value'], 'rows' => $flat];
            } else {
                $sections[] = ['label' => $label, 'headers' => ['Item', 'Value'], 'rows' => [['Value', $value]]];
            }
        }

        return $sections;
    }

    public function circulationSummary(array $params): array
    {
        $start = $params['from'] ?? now()->startOfMonth();
        $end = $params['to'] ?? now();

        return [
            'total_borrows' => BorrowRecord::whereBetween('borrowed_at', [$start, $end])->count(),
            'total_returns' => BorrowRecord::whereBetween('returned_at', [$start, $end])->count(),
            'active_borrows' => BorrowRecord::active()->count(),
            'overdue_borrows' => BorrowRecord::overdue()->count(),
            'lost_items' => BorrowRecord::where('status', 'lost')->count(),
            'damaged_items' => BorrowRecord::where('status', 'damaged')->count(),
            'period' => ['from' => $start, 'to' => $end],
        ];
    }

    public function overdueReport(array $params): array
    {
        $days = $params['min_overdue_days'] ?? 1;

        $overdueRecords = BorrowRecord::overdue()
            ->with(['user', 'bookCopy.book'])
            ->where('due_at', '<', now()->subDays($days))
            ->get();

        return [
            'total_overdue' => $overdueRecords->count(),
            'total_fines_estimated' => $overdueRecords->sum(fn ($r) => $r->daysOverdue() * 50),
            'records' => $overdueRecords->map(fn ($r) => [
                'user' => $r->user->name,
                'book' => $r->bookCopy->book->title,
                'barcode' => $r->bookCopy->barcode,
                'due_at' => $r->due_at->format('Y-m-d'),
                'days_overdue' => $r->daysOverdue(),
                'estimated_fine' => $r->daysOverdue() * 50,
            ]),
        ];
    }

    public function fineReport(array $params): array
    {
        $start = $params['from'] ?? now()->startOfMonth();
        $end = $params['to'] ?? now();

        $fines = Fine::whereBetween('created_at', [$start, $end])->get();

        return [
            'total_fines' => $fines->count(),
            'total_amount' => $fines->sum('amount'),
            'collected' => $fines->where('status', 'paid')->sum('amount'),
            'pending' => $fines->where('status', 'pending')->sum('amount'),
            'waived' => $fines->where('status', 'waived')->sum('amount'),
            'by_type' => $fines->groupBy('type')->map(fn ($g) => ['count' => $g->count(), 'amount' => $g->sum('amount')]),
        ];
    }

    public function popularBooks(array $params): array
    {
        $limit = $params['limit'] ?? 20;

        $books = Book::withCount(['copies as borrow_count' => function ($q) {
            $q->whereHas('borrowRecords');
        }])->orderByDesc('borrow_count')->limit($limit)->get();

        return [
            'books' => $books->map(fn ($b) => [
                'title' => $b->title,
                'author' => $b->author,
                'isbn' => $b->isbn,
                'total_copies' => $b->copies->count(),
                'available_copies' => $b->copies->where('status', 'available')->count(),
                'borrow_count' => $b->borrow_count,
            ]),
        ];
    }

    public function memberActivity(array $params): array
    {
        $limit = $params['limit'] ?? 20;

        $users = User::withCount('borrowRecords')
            ->orderByDesc('borrow_records_count')
            ->limit($limit)
            ->get();

        return [
            'members' => $users->map(fn ($u) => [
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->roles->pluck('name')->join(', '),
                'total_borrows' => $u->borrow_records_count,
                'active_borrows' => $u->borrowRecords()->active()->count(),
                'overdue_borrows' => $u->borrowRecords()->overdue()->count(),
            ]),
        ];
    }

    public function catalogInventory(): array
    {
        return [
            'total_books' => Book::count(),
            'total_copies' => BookCopy::count(),
            'available_copies' => BookCopy::where('status', 'available')->count(),
            'borrowed_copies' => BookCopy::where('status', 'borrowed')->count(),
            'damaged_copies' => BookCopy::where('status', 'damaged')->count(),
            'lost_copies' => BookCopy::where('status', 'lost')->count(),
            'unique_authors' => Author::count(),
            'unique_publishers' => Publisher::count(),
            'categories' => Category::count(),
        ];
    }

    public function financialSummary(array $params): array
    {
        $start = $params['from'] ?? now()->startOfYear();
        $end = $params['to'] ?? now();

        $txns = Transaction::completed()->whereBetween('paid_at', [$start, $end]);

        return [
            'total_collected' => $txns->sum('amount'),
            'by_payment_method' => $txns->get()->groupBy('payment_method')->map(fn ($g) => $g->sum('amount')),
            'by_type' => $txns->get()->groupBy('type')->map(fn ($g) => ['count' => $g->count(), 'amount' => $g->sum('amount')]),
            'monthly_breakdown' => $txns->get()
                ->groupBy(fn ($t) => $t->paid_at->format('Y-m'))
                ->map(fn ($g) => $g->sum('amount')),
        ];
    }

    public function dailyTransactions(array $params): array
    {
        $date = isset($params['date']) ? $params['date'] : today()->format('Y-m-d');

        $txns = Transaction::completed()
            ->whereDate('paid_at', $date)
            ->with('user')
            ->get();

        return [
            'date' => $date,
            'total' => $txns->sum('amount'),
            'count' => $txns->count(),
            'transactions' => $txns->map(fn ($t) => [
                'number' => $t->transaction_number,
                'user' => $t->user?->name ?? 'Unknown',
                'type' => $t->type,
                'method' => $t->payment_method,
                'amount' => $t->amount,
            ]),
        ];
    }
}
