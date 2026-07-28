<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use App\Modules\Catalog\Models\Subject;
use App\Modules\Catalog\Services\BookService;
use App\Services\ExportService;
use Illuminate\Http\Response;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class BookList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryId = null;

    public ?int $authorId = null;

    public ?int $publisherId = null;

    public ?int $subjectId = null;

    public ?int $year = null;

    public string $sort = 'title';

    public string $direction = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['except' => null, 'as' => 'category'],
        'authorId' => ['except' => null, 'as' => 'author'],
        'publisherId' => ['except' => null, 'as' => 'publisher'],
        'subjectId' => ['except' => null, 'as' => 'subject'],
        'sort' => ['except' => 'title'],
        'direction' => ['except' => 'asc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryId', 'authorId', 'publisherId', 'subjectId', 'year', 'sort', 'direction']);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('delete-books'), 403);

        $service = app(BookService::class);
        $service->delete($id);

        $this->dispatch('notify', message: 'Book deleted successfully.', type: 'success');
    }

    public function exportCsv(): Response
    {
        abort_unless(auth()->user()->can('export-books'), 403);

        return app(ExportService::class)->exportBooksCsv();
    }

    #[Computed]
    public function categories()
    {
        return Category::active()->parents()->with('children')->get();
    }

    #[Computed]
    public function authors()
    {
        return Author::active()->orderBy('name')->get();
    }

    #[Computed]
    public function publishers()
    {
        return Publisher::active()->orderBy('name')->get();
    }

    #[Computed]
    public function subjects()
    {
        return Subject::active()->orderBy('name')->get();
    }

    public function render()
    {
        $service = app(BookService::class);

        $filters = array_filter([
            'search' => $this->search,
            'category_id' => $this->categoryId,
            'author_id' => $this->authorId,
            'publisher_id' => $this->publisherId,
            'subject_id' => $this->subjectId,
            'year' => $this->year,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);

        $books = $service->searchWithFilters($filters, 12);

        return view('catalog::livewire.book-list', [
            'books' => $books,
            'categories' => $this->categories,
            'authors' => $this->authors,
            'publishers' => $this->publishers,
            'subjects' => $this->subjects,
        ]);
    }
}
