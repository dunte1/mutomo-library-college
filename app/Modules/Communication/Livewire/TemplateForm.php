<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\MessageTemplate;
use Livewire\Component;

class TemplateForm extends Component
{
    public ?int $templateId = null;

    public string $name = '';

    public string $subject = '';

    public string $body = '';

    public string $category = '';

    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'isActive' => ['boolean'],
        ];
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $template = MessageTemplate::findOrFail($id);
            $this->templateId = $template->id;
            $this->name = $template->name;
            $this->subject = $template->subject;
            $this->body = $template->body;
            $this->category = $template->category ?? '';
            $this->isActive = $template->is_active;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'subject' => $this->subject,
            'body' => $this->body,
            'category' => $this->category ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->templateId) {
            $template = MessageTemplate::findOrFail($this->templateId);
            $template->update($data);
        } else {
            $data['created_by'] = auth()->id();
            MessageTemplate::create($data);
        }

        $this->dispatch('notify', message: $this->templateId ? 'Template updated.' : 'Template created.', type: 'success');
        $this->redirect(route('communication.templates.index'), navigate: true);
    }

    public function render()
    {
        return view('communication::livewire.template-form');
    }
}
