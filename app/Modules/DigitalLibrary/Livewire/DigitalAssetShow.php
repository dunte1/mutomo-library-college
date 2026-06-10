<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Services\CitationService;
use App\Modules\DigitalLibrary\Services\SmartTagService;
use App\Services\DownloadService;
use Livewire\Component;

class DigitalAssetShow extends Component
{
    public DigitalAsset $asset;
    public ?string $citation = null;
    public string $citationStyle = 'apa';
    public array $relatedBooks = [];
    public array $tags = [];
    public string $aiSummary = '';

    public function mount(DigitalAsset $asset)
    {
        $this->asset = $asset;
        $this->asset->incrementViews();

        $this->tags = app(SmartTagService::class)->generateTags($asset);

        if ($asset->file_type === 'pdf' || $asset->file_type === 'ebook') {
            $this->aiSummary = $this->generateAISummary($asset);
        }
    }

    public function generateCitation()
    {
        $this->citation = app(CitationService::class)->generateCitation(
            $this->asset,
            $this->citationStyle
        );
    }

    public function updateProgress(int $progress, ?int $lastPage = null)
    {
        app(\App\Modules\DigitalLibrary\Services\DigitalLibraryService::class)
            ->trackProgress($this->asset->id, $progress, $lastPage);
    }

    public function download()
    {
        if (!$this->asset->allow_download) {
            session()->flash('error', 'This asset does not allow downloading.');
            return;
        }

        $this->asset->incrementDownloads();

        return app(DownloadService::class)->download(
            $this->asset,
            storage_path("app/public/{$this->asset->file_path}"),
            "{$this->asset->slug}.{$this->asset->file_extension}",
            $this->asset->title,
            'digital_asset',
            'download-digital-assets'
        );
    }

    public function setCitationStyle(string $style)
    {
        $this->citationStyle = $style;
        $this->citation = null;
    }

    private function generateAISummary(DigitalAsset $asset): string
    {
        $parts = [];

        if ($asset->author) $parts[] = "Written by {$asset->author}";
        if ($asset->publication_year) $parts[] = "published in {$asset->publication_year}";
        if ($asset->publisher) $parts[] = "by {$asset->publisher}";
        if ($asset->keywords && count($asset->keywords)) {
            $parts[] = 'covers: ' . implode(', ', array_slice($asset->keywords, 0, 5));
        }

        return $parts ? 'A ' . $asset->file_type . ' resource ' . implode(', ', $parts) . '.' : 'No description available.';
    }

    public function getFileUrlProperty()
    {
        return route('digital-library.file', $this->asset);
    }

    public function render()
    {
        return view('digital-library::livewire.digital-asset-show')->layout('layouts.app');
    }
}
