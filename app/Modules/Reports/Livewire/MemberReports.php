<?php

namespace App\Modules\Reports\Livewire;

use App\Modules\Members\Models\Member;
use Livewire\Component;

class MemberReports extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total_members' => Member::count(),
            'active_members' => Member::where('status', Member::STATUS_ACTIVE)->count(),
            'suspended_members' => Member::where('status', Member::STATUS_SUSPENDED)->count(),
            'new_this_month' => Member::where('created_at', '>=', now()->startOfMonth())->count(),
            'with_library_cards' => Member::whereHas('libraryCard')->count(),
        ];
    }

    public function render()
    {
        return view('reports::livewire.member-reports');
    }
}
