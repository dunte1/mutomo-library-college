<?php

namespace App\Modules\Reports\Livewire;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Models\Reservation;
use Livewire\Component;

class CirculationReports extends Component
{
    public array $stats = [];
    public string $period = '30';

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $dateFrom = $this->period !== 'all' ? now()->subDays((int) $this->period) : now()->subYears(10);

        $this->stats = [
            'total_borrows' => BorrowRecord::where('borrowed_at', '>=', $dateFrom)->count(),
            'active_borrows' => BorrowRecord::whereNull('returned_at')->count(),
            'overdue_borrows' => BorrowRecord::whereNull('returned_at')->where('due_at', '<', now())->count(),
            'returned_today' => BorrowRecord::whereDate('returned_at', today())->count(),
            'total_reservations' => Reservation::where('created_at', '>=', $dateFrom)->count(),
            'total_fines' => Fine::where('created_at', '>=', $dateFrom)->sum('amount'),
            'pending_fines' => Fine::where('paid', false)->sum('amount'),
        ];
    }

    public function render()
    {
        return view('reports::livewire.circulation-reports');
    }
}
