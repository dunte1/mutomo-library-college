<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Bulletin;
use Livewire\Component;
use Livewire\WithPagination;

class BulletinList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('manage-bulletins');
        $bulletin = Bulletin::findOrFail($id);
        $bulletin->delete();
        $this->dispatch('notify', message: 'Bulletin deleted successfully.', type: 'success');
    }

    public function render()
    {
        $bulletins = Bulletin::with('creator')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('content', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('communication::livewire.bulletin-list', [
            'bulletins' => $bulletins,
        ]);
    }
}
