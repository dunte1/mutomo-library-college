<?php

namespace App\Modules\Reports\Livewire;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Publisher;
use Livewire\Component;

class CatalogReports extends Component
{
    public array $stats = [];
    public array $categoryDistribution = [];
    public string $period = 'all';

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $booksQuery = Book::query();
        if ($this->period !== 'all') {
            $booksQuery->where('created_at', '>=', now()->subDays((int) $this->period));
        }

        $this->stats = [
            'total_books' => $booksQuery->count(),
            'total_copies' => BookCopy::count(),
            'available_copies' => BookCopy::where('status', 'available')->count(),
            'total_categories' => Category::count(),
            'total_authors' => Author::count(),
            'total_publishers' => Publisher::count(),
            'avg_rating' => Book::avg('rating') ?? 0,
        ];

        $this->categoryDistribution = Category::withCount('books')
            ->orderBy('books_count', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('reports::livewire.catalog-reports');
    }
}
