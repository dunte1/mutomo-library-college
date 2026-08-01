<?php

namespace App\Modules\Members\Livewire;

use App\Modules\Members\Models\LibraryCard as LibraryCardModel;
use App\Modules\Members\Models\Member;
use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class MemberLibraryCard extends Component
{
    public ?Member $member = null;

    public ?LibraryCardModel $card = null;

    public array $cardBranding = [];

    public array $displaySettings = [];

    public array $cardAuthority = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->member = Member::where('user_id', $user->id)->first();

        if ($this->member) {
            $this->card = LibraryCardModel::where('member_id', $this->member->id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        $settingsService = app(SettingsService::class);
        $this->cardBranding = $settingsService->getCardBrandingSettings();
        $this->displaySettings = $settingsService->getDisplaySettings();
        $this->cardAuthority = $settingsService->getCardAuthoritySettings();
    }

    public function downloadCard(): void
    {
        if (! $this->card) {
            $this->dispatch('notify', type: 'error', message: 'No active card to download.');

            return;
        }

        $this->redirect(route('members.card.download', $this->member->id), navigate: false);
    }

    public function reportLost(): void
    {
        abort_unless(auth()->user()->can('manage-library-cards'), 403);
        if (! $this->card || ! $this->card->isActive()) {
            return;
        }

        $this->card->markAsLost();
        $this->card = null;

        $this->dispatch('notify', type: 'success', message: 'Your card has been reported as lost. Please contact the library for a replacement.');
    }

    public function render()
    {
        return view('members::livewire.member-library-card');
    }
}
