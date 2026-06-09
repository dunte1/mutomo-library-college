<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class NewArrivals extends Component
{
    use WithPagination;

    public string $period = '30';
    public ?int $categoryId = null;

    protected $queryString = [
        'period' => ['except' => '30'],
        'categoryId' => ['except' => null, 'as' => 'category'],
    ];

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Book::with(['authors', 'category'])
            ->where('created_at', '>=', now()->subDays((int) $this->period));

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(12);

        $recentCount = Book::where('created_at', '>=', now()->subDays(7))->count();

        return view('catalog::livewire.new-arrivals', [
            'books' => $books,
            'categories' => Category::active()->parents()->with('children')->get(),
            'recentCount' => $recentCount,
        ]);
    }
}
