<?php

namespace App\Modules\Settings\Livewire;

use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentList extends Component
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
        $department = Department::findOrFail($id);
        if ($department->users()->count() > 0) {
            $this->dispatch('notify', message: 'Cannot delete department with associated users.', type: 'error');
            return;
        }
        $department->delete();
        $this->dispatch('notify', message: 'Department deleted successfully.', type: 'success');
    }

    public function render()
    {
        $departments = Department::withCount('users', 'programs')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('settings::livewire.department-list', [
            'departments' => $departments,
        ]);
    }
}
