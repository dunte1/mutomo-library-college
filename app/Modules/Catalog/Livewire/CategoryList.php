<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);
        if ($category->books()->count() > 0) {
            $this->dispatch('notify', message: 'Cannot delete category with associated books.', type: 'error');
            return;
        }
        $category->delete();
        $this->dispatch('notify', message: 'Category deleted successfully.', type: 'success');
    }

    public function render()
    {
        $categories = Category::withCount('books')
            ->with('parent')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('catalog::livewire.category-list', [
            'categories' => $categories,
        ]);
    }
}
