<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\Testimonial;
use Livewire\Component;

class TestimonialForm extends Component
{
    public ?int $testimonialId = null;

    public string $author_name = '';

    public ?string $author_role = null;

    public string $content = '';

    public ?int $rating = null;

    public string $status = 'pending';

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        if ($id) {
            $this->isEditing = true;
            $this->testimonialId = $id;
            $testimonial = Testimonial::findOrFail($id);

            $this->author_name = $testimonial->author_name;
            $this->author_role = $testimonial->author_role;
            $this->content = $testimonial->content;
            $this->rating = $testimonial->rating;
            $this->status = $testimonial->status;
            $this->sort_order = $testimonial->sort_order;
            $this->is_active = $testimonial->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'author_name' => $this->author_name,
            'author_role' => $this->author_role,
            'content' => $this->content,
            'rating' => $this->rating,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $testimonial = Testimonial::findOrFail($this->testimonialId);
            $testimonial->update($data);
            $this->dispatch('notify', message: 'Testimonial updated successfully.', type: 'success');
            $this->redirect(route('settings.testimonials'), navigate: true);
        } else {
            Testimonial::create($data);
            $this->dispatch('notify', message: 'Testimonial created successfully.', type: 'success');
            $this->redirect(route('settings.testimonials'), navigate: true);
        }
    }

    public function render()
    {
        return view('settings::livewire.testimonial-form');
    }
}
