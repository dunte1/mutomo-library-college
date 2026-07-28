<?php

namespace App\Modules\Members\Livewire;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\MemberService;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Member Details')]
class MemberShow extends Component
{
    public Member $member;

    public string $tab = 'details';

    public ?array $summary = null;

    public function mount(int $id): void
    {
        $this->member = app(MemberService::class)->find($id);
        $this->member->load(['registeredBy', 'borrowRecords.bookCopy.book', 'fines']);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function suspend(): void
    {
        abort_unless(auth()->user()->can('suspend-members'), 403);
        app(MemberService::class)->suspendMember($this->member, 'Suspended by '.auth()->user()->name);
        $this->member->refresh();
        $this->dispatch('notify', type: 'success', message: 'Member suspended successfully.');
    }

    public function activate(): void
    {
        abort_unless(auth()->user()->can('reinstate-members'), 403);
        app(MemberService::class)->activateMember($this->member);
        $this->member->refresh();
        $this->dispatch('notify', type: 'success', message: 'Member activated successfully.');
    }

    public function renew(): void
    {
        app(MemberService::class)->renewMembership($this->member);
        $this->member->refresh();
        $this->dispatch('notify', type: 'success', message: 'Membership renewed successfully.');
    }

    public function clear(): void
    {
        abort_unless(auth()->user()->can('clear-members'), 403);
        try {
            app(MemberService::class)->clearMember($this->member, 'Cleared by '.auth()->user()->name);
            $this->member->refresh();
            $this->dispatch('notify', type: 'success', message: 'Member cleared successfully.');
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can('delete-members'), 403);

        if ($this->member->activeBorrows()->count() > 0) {
            $this->dispatch('notify', type: 'error', message: 'Cannot delete member with active borrows. Return or transfer all borrowed books first.');

            return;
        }

        if ($this->member->fines()->where('status', 'pending')->count() > 0) {
            $this->dispatch('notify', type: 'error', message: 'Cannot delete member with outstanding fines. Clear all fines first.');

            return;
        }

        app(MemberService::class)->deleteMember($this->member);
        session()->flash('success', 'Member deleted successfully.');
        $this->redirect(route('members.index'), navigate: true);
    }

    public function render()
    {
        $borrowingHistory = $this->member->borrowRecords()
            ->with(['bookCopy.book'])
            ->orderBy('borrowed_at', 'desc')
            ->take(20)
            ->get();

        $fines = $this->member->fines()
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $totalBorrows = $this->member->borrowRecords()->count();
        $totalFines = $this->member->fines()->sum('amount');

        $outstandingFines = $this->member->fines()
            ->where('status', 'pending')
            ->selectRaw('COALESCE(SUM(amount), 0) - COALESCE(SUM(paid_amount), 0) - COALESCE(SUM(waived_amount), 0) as outstanding')
            ->value('outstanding');

        return view('members::livewire.member-show', compact(
            'borrowingHistory', 'fines', 'totalBorrows', 'totalFines', 'outstandingFines'
        ));
    }
}
