<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use App\Modules\Catalog\Models\Subject;
use App\Modules\Catalog\Services\BookService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookForm extends Component
{
    use WithFileUploads;

    public ?int $bookId = null;

    public string $title = '';

    public ?string $subtitle = null;

    public ?string $isbn = null;

    public ?string $description = null;

    public string $language = 'en';

    public ?int $pages = null;

    public ?int $publication_year = null;

    public ?string $edition = null;

    public ?string $volume = null;

    public ?string $series = null;

    public ?int $publisher_id = null;

    public ?int $category_id = null;

    public ?float $price = null;

    public ?string $dewey_decimal = null;

    public ?string $lc_classification = null;

    public array $authors = [];

    public array $subjects = [];

    public $cover_image = null;

    public ?string $existingCoverUrl = null;

    public ?string $shelf_location = null;

    public int $copies_count = 1;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'language' => ['required', 'string', 'size:2'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(now()->year + 1)],
            'edition' => ['nullable', 'string', 'max:100'],
            'volume' => ['nullable', 'string', 'max:100'],
            'series' => ['nullable', 'string', 'max:255'],
            'publisher_id' => ['nullable', 'integer', 'exists:publishers,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'dewey_decimal' => ['nullable', 'string', 'max:50'],
            'lc_classification' => ['nullable', 'string', 'max:50'],
            'authors' => ['nullable', 'array'],
            'authors.*' => ['integer', 'exists:authors,id'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'copies_count' => ['required_if:isEditing,false', 'integer', 'min:1', 'max:100'],
            'shelf_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updatedCoverImage()
    {
        $this->validateOnly('cover_image');
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->bookId = $id;
            $book = app(BookService::class)->find($id);

            $this->title = $book->title;
            $this->subtitle = $book->subtitle;
            $this->isbn = $book->isbn;
            $this->description = $book->description;
            $this->language = $book->language;
            $this->pages = $book->pages;
            $this->publication_year = $book->publication_year;
            $this->edition = $book->edition;
            $this->volume = $book->volume;
            $this->series = $book->series;
            $this->publisher_id = $book->publisher_id;
            $this->category_id = $book->category_id;
            $this->price = $book->price;
            $this->dewey_decimal = $book->dewey_decimal;
            $this->lc_classification = $book->lc_classification;
            $this->authors = $book->authors->pluck('id')->toArray();
            $this->subjects = $book->subjects->pluck('id')->toArray();
            if ($book->cover_image) {
                $this->existingCoverUrl = Storage::url($book->cover_image);
            }
        }
    }

    public function save(): void
    {
        $this->validate();
        $service = app(BookService::class);
        $data = $this->getFormData();

        try {
            // Handle cover image upload
            if ($this->cover_image) {
                $data['cover_image'] = $this->cover_image->store('book-covers', 'public');
            }

            if ($this->isEditing) {
                $book = $service->update($this->bookId, $data);
                $this->dispatch('notify', type: 'success', message: 'Book updated successfully.');
            } else {
                $book = $service->create($data);
                $this->dispatch('notify', type: 'success', message: 'Book created successfully.');
            }

            $this->redirect(route('catalog.books.show', $book->id), navigate: true);
        } catch (\Throwable $e) {
            Log::error('Book save failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('notify', type: 'error', message: 'An error occurred while saving the book.');
        }
    }

    protected function getFormData(): array
    {
        $data = [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'language' => $this->language,
            'pages' => $this->pages,
            'publication_year' => $this->publication_year,
            'edition' => $this->edition,
            'volume' => $this->volume,
            'series' => $this->series,
            'publisher_id' => $this->publisher_id,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'dewey_decimal' => $this->dewey_decimal,
            'lc_classification' => $this->lc_classification,
            'authors' => $this->authors,
            'subjects' => $this->subjects,
            'shelf_location' => $this->shelf_location,
        ];

        if (! $this->isEditing) {
            $data['copies_count'] = $this->copies_count;
        }

        return array_filter($data, fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    public function render()
    {
        return view('catalog::livewire.book-form', [
            'categories' => Category::active()->parents()->with('children')->orderBy('name')->get(),
            'allAuthors' => Author::active()->orderBy('name')->get(),
            'allPublishers' => Publisher::active()->orderBy('name')->get(),
            'allSubjects' => Subject::active()->orderBy('name')->get(),
        ]);
    }
}
