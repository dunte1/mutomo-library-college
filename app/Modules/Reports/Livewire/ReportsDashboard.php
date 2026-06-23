<?php

namespace App\Modules\Reports\Livewire;

use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Members\Models\Member;
use Livewire\Component;

class ReportsDashboard extends Component
{
    public array $reportStats = [];

    public function mount(): void
    {
        $this->reportStats = [
            'total_books' => Book::count(),
            'active_borrows' => BorrowRecord::whereNull('returned_at')->count(),
            'total_members' => Member::count(),
            'total_digital_assets' => DigitalAsset::count(),
            'recent_transactions' => Transaction::whereDate('created_at', today())->count(),
        ];
    }

    public function render()
    {
        return view('reports::livewire.reports-dashboard', [
            'reportStats' => $this->reportStats,
        ]);
    }
}
