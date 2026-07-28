<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Publisher;
use Livewire\Component;
use Livewire\WithPagination;

class PublisherList extends Component
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
        abort_unless(auth()->user()->can('delete-publishers'), 403);

        $publisher = Publisher::findOrFail($id);
        if ($publisher->books()->count() > 0) {
            $this->dispatch('notify', message: 'Cannot delete publisher with associated books. Remove all books from this publisher first.', type: 'error');

            return;
        }
        $publisher->delete();
        $this->dispatch('notify', message: 'Publisher deleted successfully.', type: 'success');
    }

    public function render()
    {
        $publishers = Publisher::withCount('books')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('catalog::livewire.publisher-list', [
            'publishers' => $publishers,
        ]);
    }
}
