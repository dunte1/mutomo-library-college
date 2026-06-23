<?php

namespace App\Modules\Settings\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleForm extends Component
{
    public ?int $roleId = null;
    public bool $isEditing = false;

    public string $name = '';
    public string $guard_name = 'web';
    public array $selectedPermissions = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->roleId = $id;
            $this->isEditing = true;
            $role = Role::findOrFail($id);
            $this->name = $role->name;
            $this->guard_name = $role->guard_name;
            $this->selectedPermissions = $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        }
    }

    public function rules(): array
    {
        $unique = \Illuminate\Validation\Rule::unique('roles', 'name')->where('guard_name', $this->guard_name);
        if ($this->isEditing) {
            $unique = $unique->ignore($this->roleId);
        }

        return [
            'name' => ['required', 'string', 'max:255', 'alpha_dash', $unique],
            'guard_name' => ['required', 'string', 'in:web,api'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['exists:permissions,id'],
        ];
    }

    public function toggleGroup(string $group): void
    {
        $groupPermissions = Permission::where('name', 'like', "{$group}-%")
            ->where('guard_name', $this->guard_name)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $allSelected = empty(array_diff($groupPermissions, $this->selectedPermissions));

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $groupPermissions));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $groupPermissions)));
        }
    }

    public function save(): void
    {
        $this->validate();

        if ($this->isEditing) {
            $role = Role::findOrFail($this->roleId);
            if (in_array($role->name, ['super-admin', 'admin']) && $this->name !== $role->name) {
                session()->flash('error', 'Cannot rename core system roles.');
                return;
            }
            $role->update(['name' => $this->name, 'guard_name' => $this->guard_name]);
            $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            session()->flash('success', 'Role updated successfully.');
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => $this->guard_name]);
            $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            session()->flash('success', 'Role created successfully.');
        }

        $this->redirect(route('settings.roles'), navigate: true);
    }

    public function render()
    {
        $permissions = Permission::where('guard_name', $this->guard_name)
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                $parts = explode('-', $permission->name, 2);
                return $parts[0] ?? 'other';
            });

        return view('settings::livewire.role-form', [
            'groupedPermissions' => $permissions,
        ]);
    }
}
