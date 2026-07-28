<?php

namespace App\Modules\Settings\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $role = '';

    public string $status = '';

    protected $queryString = ['search', 'role', 'status'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'role', 'status']);
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('manage-settings');
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            session()->flash('error', 'Cannot deactivate a super admin.');

            return;
        }
        $user->update(['is_active' => ! $user->is_active]);
        session()->flash('success', 'User status updated.');
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('admission_number', 'like', "%{$this->search}%")
                    ->orWhere('employee_id', 'like', "%{$this->search}%");
            }))
            ->when($this->role, fn ($q) => $q->role($this->role))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === 'active'))
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::orderBy('name')->get();
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'admins' => User::role(['super-admin', 'admin'])->count(),
            'librarians' => User::role('librarian')->count(),
        ];

        return view('settings::livewire.user-list', [
            'users' => $users,
            'roles' => $roles,
            'stats' => $stats,
        ]);
    }
}
