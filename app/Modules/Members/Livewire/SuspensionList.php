<?php

namespace App\Modules\Members\Livewire;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\MemberService;
use Livewire\Component;
use Livewire\WithPagination;

class SuspensionList extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $reason = null;
    public ?int $reinstateMemberId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function confirmReinstate(int $memberId): void
    {
        $this->reinstateMemberId = $memberId;
    }

    public function reinstate(): void
    {
        try {
            $this->authorize('reinstate-members');

            $member = Member::findOrFail($this->reinstateMemberId);
            $member->update(['status' => Member::STATUS_ACTIVE]);
            $this->reinstateMemberId = null;
            $this->dispatch('notify', type: 'success', message: 'Member reinstated successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to reinstate member: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Member::suspended()->with('user');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('member_id', 'like', "%{$this->search}%")
                  ->orWhere('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%");
            });
        }

        $members = $query->orderBy('updated_at', 'desc')->paginate(15);

        $stats = [
            'total' => Member::suspended()->count(),
        ];

        return view('members::livewire.suspension-list', [
            'members' => $members,
            'stats' => $stats,
        ]);
    }
}
