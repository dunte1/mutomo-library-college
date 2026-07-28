<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use Livewire\Component;

class DigitalAssetEdit extends Component
{
    public DigitalAsset $asset;

    public string $title = '';

    public string $description = '';

    public ?int $categoryId = null;

    public string $author = '';

    public string $publisher = '';

    public ?int $publicationYear = null;

    public string $language = 'en';

    public string $keywords = '';

    public string $accessLevel = 'restricted';

    public bool $allowDownload = true;

    public bool $allowPrinting = false;

    public bool $isActive = true;

    public bool $isFeatured = false;

    public function mount(DigitalAsset $asset): void
    {
        abort_unless(auth()->user()->can('edit-digital-assets'), 403);
        $this->asset = $asset;
        $this->title = $asset->title;
        $this->description = $asset->description ?? '';
        $this->categoryId = $asset->category_id;
        $this->author = $asset->author ?? '';
        $this->publisher = $asset->publisher ?? '';
        $this->publicationYear = $asset->publication_year;
        $this->language = $asset->language ?? 'en';
        $this->keywords = is_array($asset->keywords) ? implode(', ', $asset->keywords) : ($asset->keywords ?? '');
        $this->accessLevel = $asset->access_level ?? 'restricted';
        $this->allowDownload = $asset->allow_download;
        $this->allowPrinting = $asset->allow_printing;
        $this->isActive = $asset->is_active;
        $this->isFeatured = $asset->is_featured;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'accessLevel' => 'required|in:public,restricted,premium',
        ]);

        $this->asset->update([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'category_id' => $this->categoryId,
            'author' => $this->author ?: null,
            'publisher' => $this->publisher ?: null,
            'publication_year' => $this->publicationYear,
            'language' => $this->language,
            'keywords' => $this->keywords ? array_map('trim', explode(',', $this->keywords)) : null,
            'access_level' => $this->accessLevel,
            'allow_download' => $this->allowDownload,
            'allow_printing' => $this->allowPrinting,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ]);

        session()->flash('success', "Asset '{$this->title}' updated successfully.");
        $this->redirect(route('digital-library.show', $this->asset), navigate: true);
    }

    public function render()
    {
        return view('digital-library::livewire.digital-asset-edit', [
            'categories' => DigitalAssetCategory::active()->get(),
        ])->layout('layouts.app');
    }
}
