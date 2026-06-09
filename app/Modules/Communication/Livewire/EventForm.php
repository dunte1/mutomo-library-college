<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Event;
use Livewire\Component;

class EventForm extends Component
{
    public ?int $eventId = null;
    public string $title = '';
    public string $description = '';
    public string $location = '';
    public ?string $start_date = null;
    public ?string $end_date = null;
    public string $type = 'other';
    public string $status = 'upcoming';

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->eventId = $id;
            $event = Event::findOrFail($id);

            $this->title = $event->title;
            $this->description = $event->description;
            $this->location = $event->location;
            $this->start_date = $event->start_date?->format('Y-m-d\TH:i');
            $this->end_date = $event->end_date?->format('Y-m-d\TH:i');
            $this->type = $event->type;
            $this->status = $event->status;
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'type' => ['required', 'in:academic,cultural,sports,workshop,other'],
            'status' => ['required', 'in:upcoming,ongoing,completed,cancelled'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'type' => $this->type,
            'status' => $this->status,
            'created_by' => auth()->id(),
        ];

        if ($this->isEditing) {
            $event = Event::findOrFail($this->eventId);
            $event->update($data);
            $this->dispatch('notify', message: 'Event updated successfully.', type: 'success');
            $this->redirect(route('communication.events.index'), navigate: true);
        } else {
            Event::create($data);
            $this->dispatch('notify', message: 'Event created successfully.', type: 'success');
            $this->redirect(route('communication.events.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('communication::livewire.event-form');
    }
}
