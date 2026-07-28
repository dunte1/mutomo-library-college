<?php

namespace App\Modules\Settings\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $guard = 'web';

    protected $queryString = ['search', 'guard'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('manage-roles');
        $role = Role::findOrFail($id);
        if (in_array($role->name, ['super-admin', 'admin'])) {
            session()->flash('error', 'Cannot delete core system roles.');

            return;
        }
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        session()->flash('success', 'Role deleted successfully.');
    }

    public function render()
    {
        $roles = Role::with('permissions')
            ->where('guard_name', $this->guard)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        $permissionCount = Permission::where('guard_name', $this->guard)->count();

        return view('settings::livewire.role-list', [
            'roles' => $roles,
            'permissionCount' => $permissionCount,
        ]);
    }
}
