<?php

namespace App\Modules\Members\Livewire;

use App\Modules\Members\Models\LibraryCard as LibraryCardModel;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use Livewire\Component;

class LibraryCard extends Component
{
    public Member $member;
    public ?LibraryCardModel $card = null;
    public string $tab = 'view';
    public array $cardStats = [];

    protected $listeners = ['cardGenerated' => '$refresh'];

    public function mount(int $id): void
    {
        $this->member = Member::findOrFail($id);
        $this->card = LibraryCardModel::where('member_id', $id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $this->cardStats = app(LibraryCardService::class)->getCardStats();
    }

    public function generateCard(): void
    {
        $this->authorize('manage-library-cards');

        try {
            app(LibraryCardService::class)->issueCard($this->member, auth()->user());
            $this->card = LibraryCardModel::where('member_id', $this->member->id)
                ->where('status', 'active')
                ->latest()
                ->first();
            $this->dispatch('notify', type: 'success', message: 'Library card generated successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to generate card: ' . $e->getMessage());
        }
    }

    public function reissueCard(): void
    {
        $this->authorize('manage-library-cards');

        if (!$this->card) {
            $this->dispatch('notify', type: 'error', message: 'No active card to reissue.');
            return;
        }

        try {
            $this->card = app(LibraryCardService::class)->reissueCard($this->card, auth()->user());
            $this->dispatch('notify', type: 'success', message: 'Card reissued successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to reissue card: ' . $e->getMessage());
        }
    }

    public function markAsLost(): void
    {
        $this->authorize('manage-library-cards');

        if (!$this->card || !$this->card->isActive()) {
            $this->dispatch('notify', type: 'error', message: 'No active card to mark as lost.');
            return;
        }

        $this->card->markAsLost();
        $this->card = null;

        activity()
            ->performedOn($this->card)
            ->causedBy(auth()->user())
            ->log("Library card marked as lost for {$this->member->full_name}");

        $this->dispatch('notify', type: 'success', message: 'Card marked as lost.');
    }

    public function downloadCard(): void
    {
        if (!$this->card) {
            $this->dispatch('notify', type: 'error', message: 'No active card to download.');
            return;
        }

        $this->redirect(route('members.card.download', $this->member->id), navigate: false);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $history = LibraryCardModel::where('member_id', $this->member->id)
            ->orderByDesc('created_at')
            ->get();

        return view('members::livewire.library-card', [
            'history' => $history,
        ]);
    }
}
