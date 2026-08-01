<?php

namespace App\Modules\Members\Livewire;

use App\Modules\Members\Models\LibraryCard as LibraryCardModel;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;
use Livewire\WithFileUploads;

class LibraryCard extends Component
{
    use WithFileUploads;

    public Member $member;

    public ?LibraryCardModel $card = null;

    public mixed $passportPhoto = null;

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

        $this->validate([
            'passportPhoto' => 'nullable|image|max:2048',
        ]);

        try {
            $photoPath = $this->uploadAndSyncPhoto();

            app(LibraryCardService::class)->issueCard($this->member, auth()->user(), $photoPath);
            $this->card = LibraryCardModel::where('member_id', $this->member->id)
                ->where('status', 'active')
                ->latest()
                ->first();
            $this->passportPhoto = null;
            $this->dispatch('notify', type: 'success', message: 'Library card generated successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to generate card: '.$e->getMessage());
        }
    }

    public function reissueCard(): void
    {
        $this->authorize('manage-library-cards');

        if (! $this->card) {
            $this->dispatch('notify', type: 'error', message: 'No active card to reissue.');

            return;
        }

        $this->validate([
            'passportPhoto' => 'nullable|image|max:2048',
        ]);

        try {
            $photoPath = $this->uploadAndSyncPhoto();

            $this->card = app(LibraryCardService::class)->reissueCard($this->card, auth()->user(), $photoPath);
            $this->passportPhoto = null;
            $this->dispatch('notify', type: 'success', message: 'Card reissued successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to reissue card: '.$e->getMessage());
        }
    }

    protected function uploadAndSyncPhoto(): ?string
    {
        if (! $this->passportPhoto) {
            return null;
        }

        $path = $this->passportPhoto->store('members/photos', 'public');

        $this->member->update(['photo' => $path]);

        return $path;
    }

    public function markAsLost(): void
    {
        $this->authorize('manage-library-cards');

        if (! $this->card || ! $this->card->isActive()) {
            $this->dispatch('notify', type: 'error', message: 'No active card to mark as lost.');

            return;
        }

        $card = $this->card;
        $card->markAsLost();
        $this->card = null;

        activity()
            ->performedOn($card)
            ->causedBy(auth()->user())
            ->log("Library card marked as lost for {$this->member->full_name}");

        $this->dispatch('notify', type: 'success', message: 'Card marked as lost.');
    }

    public function downloadCard(): void
    {
        if (! $this->card) {
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

        $settingsService = app(SettingsService::class);
        $cardBranding = $settingsService->getCardBrandingSettings();
        $displaySettings = $settingsService->getDisplaySettings();
        $cardAuthority = $settingsService->getCardAuthoritySettings();

        return view('members::livewire.library-card', [
            'history' => $history,
            'cardBranding' => $cardBranding,
            'displaySettings' => $displaySettings,
            'cardAuthority' => $cardAuthority,
        ]);
    }
}
