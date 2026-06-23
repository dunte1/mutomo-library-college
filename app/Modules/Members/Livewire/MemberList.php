<?php

namespace App\Modules\Members\Livewire;

use App\Modules\Members\Services\MemberService;
use App\Services\ExportService;
use Illuminate\Http\Response;
use Livewire\Component;
use Livewire\WithPagination;

class MemberList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $membershipType = '';

    public string $sort = 'created_at';

    public string $direction = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'membershipType' => ['except' => '', 'as' => 'type'],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingMembershipType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'membershipType', 'sort', 'direction']);
    }

    public function exportCsv(): Response
    {
        return app(ExportService::class)->exportMembersCsv();
    }

    public function render()
    {
        $service = app(MemberService::class);

        $filters = array_filter([
            'search' => $this->search,
            'status' => $this->status,
            'membership_type' => $this->membershipType,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);

        $members = $service->searchWithFilters($filters, 15);
        $stats = $service->getMemberStats();

        return view('members::livewire.member-list', [
            'members' => $members,
            'stats' => $stats,
        ]);
    }
}
