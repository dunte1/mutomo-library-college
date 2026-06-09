<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sort = 'books.title';
    public string $direction = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sort' => ['except' => 'books.title'],
        'direction' => ['except' => 'asc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'direction']);
    }

    public function render()
    {
        $query = BookCopy::with(['book', 'currentBorrow'])
            ->join('books', 'book_copies.book_id', '=', 'books.id')
            ->select('book_copies.*');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('books.title', 'like', "%{$this->search}%")
                  ->orWhere('book_copies.barcode', 'like', "%{$this->search}%")
                  ->orWhere('book_copies.shelf_location', 'like', "%{$this->search}%");
            });
        }

        if ($this->status) {
            $query->where('book_copies.status', $this->status);
        }

        $query->orderBy($this->sort, $this->direction);

        $copies = $query->paginate(15);

        $stats = [
            'total' => BookCopy::count(),
            'available' => BookCopy::where('status', 'available')->count(),
            'borrowed' => BookCopy::where('status', 'borrowed')->count(),
            'damaged' => BookCopy::where('status', 'damaged')->count(),
            'lost' => BookCopy::where('status', 'lost')->count(),
        ];

        return view('catalog::livewire.inventory-list', [
            'copies' => $copies,
            'stats' => $stats,
        ]);
    }
}
