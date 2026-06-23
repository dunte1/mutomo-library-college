<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\Catalog\Models\Book;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use App\Modules\DigitalLibrary\Services\DigitalLibraryService;
use Livewire\Component;
use Livewire\WithFileUploads;

class DigitalAssetUpload extends Component
{
    use WithFileUploads;

    public $file;

    public $coverImage;

    public string $title = '';

    public ?string $description = null;

    public ?string $categoryId = null;

    public ?string $author = null;

    public ?string $publisher = null;

    public ?string $isbn = null;

    public ?string $publicationYear = null;

    public string $language = 'en';

    public ?string $keywords = null;

    public string $accessLevel = 'restricted';

    public bool $allowDownload = true;

    public bool $allowPrinting = false;

    public bool $isActive = true;

    public ?string $bookId = null;

    protected $rules = [
        'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,epub,mobi,mp4,mp3,jpeg,png,jpg,gif,webp,txt,csv,zip|max:102400',
        'coverImage' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:5000',
        'categoryId' => 'nullable|exists:digital_asset_categories,id',
        'author' => 'nullable|string|max:255',
        'publisher' => 'nullable|string|max:255',
        'isbn' => 'nullable|string|max:20',
        'publicationYear' => 'nullable|integer|min:1900|max:2099',
        'language' => 'required|string|size:2',
        'keywords' => 'nullable|string|max:500',
        'accessLevel' => 'required|in:public,restricted,private',
        'allowDownload' => 'boolean',
        'allowPrinting' => 'boolean',
        'bookId' => 'nullable|exists:books,id',
    ];

    public function save()
    {
        $this->validate();

        $coverPath = null;
        if ($this->coverImage) {
            $coverPath = $this->coverImage->store('digital-library/covers', 'public');
        }

        $service = app(DigitalLibraryService::class);
        $asset = $service->upload($this->file, [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'publication_year' => $this->publicationYear,
            'language' => $this->language,
            'keywords' => $this->keywords,
            'access_level' => $this->accessLevel,
            'allow_download' => $this->allowDownload,
            'allow_printing' => $this->allowPrinting,
            'is_active' => $this->isActive,
            'cover_image' => $coverPath,
            'book_id' => $this->bookId ?: null,
        ]);

        session()->flash('success', "Digital asset '{$asset->title}' uploaded successfully.");

        return $this->redirect(route('digital-library.show', $asset), navigate: true);
    }

    public function render()
    {
        return view('digital-library::livewire.digital-asset-upload', [
            'categories' => DigitalAssetCategory::active()->get(),
            'books' => Book::active()->orderBy('title')->get(),
        ])->layout('layouts.app');
    }
}
