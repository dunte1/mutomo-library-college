<?php

namespace App\Modules\Members\Livewire;

use App\Mail\WelcomeCredentials;
use App\Models\User;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class MembershipRequestList extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-membership-requests'), 403);
    }

    public function approve(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $member = $user->member;

            $user->update(['email_verified_at' => now()]);

            $role = match ($member?->membership_type) {
                'teacher' => 'lecturer',
                'staff' => 'staff',
                default => 'student',
            };
            $user->assignRole($role);

            if ($member) {
                $member->update(['status' => Member::STATUS_ACTIVE]);
                if (! $member->libraryCard) {
                    app(LibraryCardService::class)->autoIssueCard($member);
                }
            }

            $tempPassword = \Illuminate\Support\Str::random(12);
            $user->update(['password' => $tempPassword]);
            Mail::to($user->email)->queue(new WelcomeCredentials($user, $tempPassword));

            $this->dispatch('notify', type: 'success', message: 'Membership approved. Role assigned, library card issued, and welcome email sent.');
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
