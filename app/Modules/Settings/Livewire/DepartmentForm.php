<?php

namespace App\Modules\Settings\Livewire;

use App\Models\Department;
use Livewire\Component;

class DepartmentForm extends Component
{
    public ?int $departmentId = null;
    public string $name = '';
    public string $code = '';
    public ?string $description = null;
    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->departmentId = $id;
            $department = Department::findOrFail($id);

            $this->name = $department->name;
            $this->code = $department->code;
            $this->description = $department->description;
            $this->is_active = $department->is_active;
        }
    }

    public function rules(): array
    {
        $uniqueCode = 'unique:departments,code';
        if ($this->isEditing) {
            $uniqueCode .= ',' . $this->departmentId;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', $uniqueCode],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            Department::findOrFail($this->departmentId)->update($data);
            $this->dispatch('notify', message: 'Department updated successfully.', type: 'success');
            $this->redirect(route('settings.departments'), navigate: true);
        } else {
            Department::create($data);
            $this->dispatch('notify', message: 'Department created successfully.', type: 'success');
            $this->redirect(route('settings.departments'), navigate: true);
        }
    }

    public function render()
    {
        return view('settings::livewire.department-form');
    }
}
