<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Announcement;
use Livewire\Component;

class AnnouncementForm extends Component
{
    public ?int $announcementId = null;
    public string $title = '';
    public string $content = '';
    public string $type = 'info';
    public string $status = 'draft';
    public ?string $published_at = null;
    public ?string $expires_at = null;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->announcementId = $id;
            $announcement = Announcement::findOrFail($id);

            $this->title = $announcement->title;
            $this->content = $announcement->content;
            $this->type = $announcement->type;
            $this->status = $announcement->status;
            $this->published_at = $announcement->published_at?->format('Y-m-d\TH:i');
            $this->expires_at = $announcement->expires_at?->format('Y-m-d\TH:i');
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:info,warning,important'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
            'created_by' => auth()->id(),
        ];

        if ($this->isEditing) {
            $announcement = Announcement::findOrFail($this->announcementId);
            $announcement->update($data);
            $this->dispatch('notify', message: 'Announcement updated successfully.', type: 'success');
            $this->redirect(route('communication.announcements.index'), navigate: true);
        } else {
            Announcement::create($data);
            $this->dispatch('notify', message: 'Announcement created successfully.', type: 'success');
            $this->redirect(route('communication.announcements.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('communication::livewire.announcement-form');
    }
}
