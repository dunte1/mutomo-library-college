<?php

namespace App\Modules\Settings\Livewire;

use App\Models\Program;
use Livewire\Component;
use Livewire\WithPagination;

class ProgramList extends Component
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
        $program = Program::findOrFail($id);
        $program->delete();
        session()->flash('success', 'Program deleted successfully.');
    }

    public function render()
    {
        $programs = Program::with('department')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('settings::livewire.program-list', [
            'programs' => $programs,
        ]);
    }
}
