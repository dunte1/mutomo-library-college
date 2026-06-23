<?php

namespace App\Modules\Settings\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    public ?int $userId = null;
    public bool $isEditing = false;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public ?string $admission_number = null;
    public ?string $employee_id = null;
    public bool $is_active = true;
    public array $selectedRoles = [];
    public ?string $department_id = null;
    public ?string $program_id = null;

    public string $password = '';
    public string $password_confirmation = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->userId = $id;
            $this->isEditing = true;
            $user = User::findOrFail($id);
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->admission_number = $user->admission_number;
            $this->employee_id = $user->employee_id;
            $this->is_active = $user->is_active;
            $this->department_id = $user->department_id;
            $this->program_id = $user->program_id;
            $this->selectedRoles = $user->roles->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        }
    }

    public function rules(): array
    {
        $uniqueEmail = Rule::unique('users', 'email');
        $uniqueAdmission = Rule::unique('users', 'admission_number');
        $uniqueEmployee = Rule::unique('users', 'employee_id');

        if ($this->isEditing) {
            $uniqueEmail = $uniqueEmail->ignore($this->userId);
            $uniqueAdmission = $uniqueAdmission->ignore($this->userId);
            $uniqueEmployee = $uniqueEmployee->ignore($this->userId);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $uniqueEmail],
            'phone' => ['nullable', 'string', 'max:20'],
            'admission_number' => ['nullable', 'string', 'max:50', $uniqueAdmission],
            'employee_id' => ['nullable', 'string', 'max:50', $uniqueEmployee],
            'is_active' => ['boolean'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
        ];

        if (!$this->isEditing) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'is_active' => $this->is_active,
            'department_id' => $this->department_id ?: null,
            'program_id' => $this->program_id ?: null,
        ];

        if ($this->admission_number) {
            $data['admission_number'] = $this->admission_number;
        }
        if ($this->employee_id) {
            $data['employee_id'] = $this->employee_id;
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditing) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            $user->syncRoles($this->selectedRoles);
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            session()->flash('success', 'User updated successfully.');
        } else {
            $data['password'] = Hash::make($this->password);
            $data['email_verified_at'] = now();
            $user = User::create($data);
            $user->assignRole($this->selectedRoles);
            session()->flash('success', 'User created successfully.');
        }

        $this->redirect(route('settings.users'), navigate: true);
    }

    public function render()
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        $programs = \App\Models\Program::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();

        return view('settings::livewire.user-form', [
            'departments' => $departments,
            'programs' => $programs,
            'roles' => $roles,
        ]);
    }
}
