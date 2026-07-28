<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Services\BorrowingService;
use App\Services\ExportService;
use Illuminate\Http\Response;
use Livewire\Component;
use Livewire\WithPagination;

class CirculationList extends Component
{
    use WithPagination;

    public string $tab = 'active';

    public string $search = '';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function exportCsv(): Response
    {
        abort_unless(auth()->user()->can('view-circulation'), 403);
        return app(ExportService::class)->exportCirculationCsv($this->tab);
    }

    public function markAsLost(int $id): void
    {
        abort_unless(auth()->user()->can('view-circulation'), 403);
        $record = BorrowRecord::with('bookCopy.book')->findOrFail($id);
        app(BorrowingService::class)->markAsLost($record);
        $this->dispatch('notify', message: 'Book marked as lost. Fine assessed.', type: 'success');
    }

    public function markAsDamaged(int $id): void
    {
        abort_unless(auth()->user()->can('view-circulation'), 403);
        $record = BorrowRecord::with('bookCopy.book')->findOrFail($id);
        app(BorrowingService::class)->markAsDamaged($record);
        $this->dispatch('notify', message: 'Book marked as damaged. Fine assessed.', type: 'success');
    }

    public function render()
    {
        $service = app(BorrowingService::class);
        $perPage = 15;

        switch ($this->tab) {
            case 'overdue':
                $records = $service->getOverdueBorrows($perPage);
                break;
            case 'history':
                $records = BorrowRecord::with(['user', 'bookCopy.book'])
                    ->orderBy('borrowed_at', 'desc')
                    ->paginate($perPage);
                break;
            default:
                $records = $service->getActiveBorrows($perPage);
                break;
        }

        $stats = $service->getStatistics();

        return view('circulation::livewire.circulation-list', [
            'records' => $records,
            'stats' => $stats,
        ]);
    }
}
