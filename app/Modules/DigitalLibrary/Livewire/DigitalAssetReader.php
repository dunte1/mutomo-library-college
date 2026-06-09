<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Services\DigitalLibraryService;
use Livewire\Component;

class DigitalAssetReader extends Component
{
    public DigitalAsset $asset;
    public int $currentPage = 1;
    public int $totalPages = 0;
    public int $progress = 0;
    public float $zoom = 1.0;
    public bool $fullscreen = false;
    public bool $showSidebar = true;

    public function mount(DigitalAsset $asset)
    {
        $this->asset = $asset;
        $this->asset->incrementViews();

        $history = \App\Modules\DigitalLibrary\Models\ReadingHistory::where('user_id', auth()->id())
            ->where('digital_asset_id', $asset->id)
            ->first();

        if ($history) {
            $this->currentPage = max(1, $history->last_page ?? 1);
            $this->progress = $history->progress ?? 0;
        }
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = max(1, min($page, max($this->totalPages, 1)));
        $this->updateProgress();
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->updateProgress();
        }
    }

    public function prevPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->updateProgress();
        }
    }

    public function zoomIn(): void
    {
        $this->zoom = min(3.0, $this->zoom + 0.25);
    }

    public function zoomOut(): void
    {
        $this->zoom = max(0.5, $this->zoom - 0.25);
    }

    public function zoomReset(): void
    {
        $this->zoom = 1.0;
    }

    public function toggleSidebar(): void
    {
        $this->showSidebar = !$this->showSidebar;
    }

    protected function updateProgress(): void
    {
        if ($this->totalPages > 0) {
            $this->progress = (int) round(($this->currentPage / $this->totalPages) * 100);
        }

        app(DigitalLibraryService::class)->trackProgress(
            $this->asset->id,
            $this->progress,
            $this->currentPage
        );
    }

    public function getFileUrlProperty(): string
    {
        return url('storage/' . $this->asset->file_path);
    }

    public function getIsReadOnlyProperty(): bool
    {
        return !$this->asset->allow_download && !$this->asset->allow_printing;
    }

    public function render()
    {
        return view('digital-library::livewire.digital-asset-reader')
            ->layout('layouts.app');
    }
}
