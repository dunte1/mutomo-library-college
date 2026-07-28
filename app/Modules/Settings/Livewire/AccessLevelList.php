<?php

namespace App\Modules\Settings\Livewire;

use App\Models\AccessLevel;
use Livewire\Component;
use Livewire\WithPagination;

class AccessLevelList extends Component
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
        abort_unless(auth()->user()->can('manage-access-levels'), 403);
        $accessLevel = AccessLevel::findOrFail($id);
        $accessLevel->delete();
        $this->dispatch('notify', message: 'Access level deleted successfully.', type: 'success');
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()->can('manage-access-levels'), 403);
        $accessLevel = AccessLevel::findOrFail($id);
        $accessLevel->update(['is_active' => ! $accessLevel->is_active]);
        $this->dispatch('notify', message: 'Access level status updated successfully.', type: 'success');
    }

    public function render()
    {
        $accessLevels = AccessLevel::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
            ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('settings::livewire.access-level-list', [
            'accessLevels' => $accessLevels,
        ]);
    }
}
