<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Author;
use Livewire\Component;
use Livewire\WithPagination;

class AuthorList extends Component
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
        abort_unless(auth()->user()->can('delete-authors'), 403);

        $author = Author::findOrFail($id);
        if ($author->books()->count() > 0) {
            $this->dispatch('notify', message: 'Cannot delete author with associated books. Remove all books by this author first.', type: 'error');

            return;
        }
        $author->delete();
        $this->dispatch('notify', message: 'Author deleted successfully.', type: 'success');
    }

    public function render()
    {
        $authors = Author::withCount('books')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('catalog::livewire.author-list', [
            'authors' => $authors,
        ]);
    }
}
