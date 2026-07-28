<?php

namespace App\Modules\Communication\Livewire;

use App\Models\Department;
use App\Modules\Communication\Models\Bulletin;
use Livewire\Component;

class BulletinForm extends Component
{
    public ?int $bulletinId = null;

    public string $title = '';

    public string $content = '';

    public ?int $department_id = null;

    public string $status = 'draft';

    public ?string $published_at = null;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        abort_unless(auth()->user()->can('manage-bulletins'), 403);
        if ($id) {
            $this->isEditing = true;
            $this->bulletinId = $id;
            $bulletin = Bulletin::findOrFail($id);

            $this->title = $bulletin->title;
            $this->content = $bulletin->content;
            $this->department_id = $bulletin->department_id;
            $this->status = $bulletin->status;
            $this->published_at = $bulletin->published_at?->format('Y-m-d\TH:i');
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'department_id' => $this->department_id,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'created_by' => auth()->id(),
        ];

        if ($this->isEditing) {
            $bulletin = Bulletin::findOrFail($this->bulletinId);
            $bulletin->update($data);
            $this->dispatch('notify', message: 'Bulletin updated successfully.', type: 'success');
            $this->redirect(route('communication.bulletins.index'), navigate: true);
        } else {
            Bulletin::create($data);
            $this->dispatch('notify', message: 'Bulletin created successfully.', type: 'success');
            $this->redirect(route('communication.bulletins.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('communication::livewire.bulletin-form', [
            'departments' => Department::active()->orderBy('name')->get(),
        ]);
    }
}
