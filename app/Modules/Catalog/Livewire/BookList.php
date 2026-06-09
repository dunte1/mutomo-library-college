<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use App\Modules\Catalog\Models\Subject;
use App\Modules\Catalog\Services\BookService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\ExportService;

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

    public function exportCsv(): \Illuminate\Http\Response
    {
        return app(ExportService::class)->exportBooksCsv();
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
            'categories' => Category::active()->parents()->with('children')->get(),
            'authors' => Author::active()->orderBy('name')->get(),
            'publishers' => Publisher::active()->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
        ]);
    }
}
