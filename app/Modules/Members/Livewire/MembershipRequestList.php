<?php

namespace App\Modules\Members\Livewire;

use App\Models\User;
use App\Modules\Members\Models\Member;
use Livewire\Component;
use Livewire\WithPagination;

class MembershipRequestList extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function approve(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $user->update(['email_verified_at' => now()]);
            $this->dispatch('notify', type: 'success', message: 'Membership approved successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to approve membership: '.$e->getMessage());
        }
    }

    public function reject(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $member = $user->member;
            if ($member) {
                $member->update(['status' => Member::STATUS_INACTIVE]);
            }
            $this->dispatch('notify', type: 'warning', message: 'Membership request rejected.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to reject membership: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = User::whereDoesntHave('roles')
            ->orWhereHas('member', fn ($q) => $q->where('status', Member::STATUS_INACTIVE));

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('members::livewire.membership-request-list', [
            'requests' => $requests,
        ]);
    }
}
