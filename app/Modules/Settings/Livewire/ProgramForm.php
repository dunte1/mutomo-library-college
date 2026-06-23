<?php

namespace App\Modules\Settings\Livewire;

use App\Models\Department;
use App\Models\Program;
use Livewire\Component;

class ProgramForm extends Component
{
    public ?int $programId = null;

    public string $name = '';

    public string $code = '';

    public ?int $department_id = null;

    public ?string $description = null;

    public int $duration_years = 3;

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->programId = $id;
            $program = Program::findOrFail($id);

            $this->name = $program->name;
            $this->code = $program->code;
            $this->department_id = $program->department_id;
            $this->description = $program->description;
            $this->duration_years = $program->duration_years;
            $this->is_active = $program->is_active;
        }
    }

    public function rules(): array
    {
        $uniqueCode = 'unique:programs,code';
        if ($this->isEditing) {
            $uniqueCode .= ','.$this->programId;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', $uniqueCode],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'duration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'department_id' => $this->department_id,
            'description' => $this->description,
            'duration_years' => $this->duration_years,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            Program::findOrFail($this->programId)->update($data);
            session()->flash('success', 'Program updated successfully.');
            $this->redirect(route('settings.programs'), navigate: true);
        } else {
            Program::create($data);
            session()->flash('success', 'Program created successfully.');
            $this->redirect(route('settings.programs'), navigate: true);
        }
    }

    public function render()
    {
        return view('settings::livewire.program-form', [
            'departments' => Department::active()->orderBy('name')->get(),
        ]);
    }
}
