<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Author;
use Illuminate\Support\Str;
use Livewire\Component;

class AuthorForm extends Component
{
    public ?int $authorId = null;

    public string $name = '';

    public ?string $biography = null;

    public ?string $birth_date = null;

    public ?string $death_date = null;

    public ?string $nationality = null;

    public ?string $website = null;

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            abort_unless(auth()->user()->can('edit-authors'), 403);
            $this->isEditing = true;
            $this->authorId = $id;
            $author = Author::findOrFail($id);

            $this->name = $author->name;
            $this->biography = $author->biography;
            $this->birth_date = $author->birth_date?->format('Y-m-d');
            $this->death_date = $author->death_date?->format('Y-m-d');
            $this->nationality = $author->nationality;
            $this->website = $author->website;
            $this->is_active = $author->is_active;
        } else {
            abort_unless(auth()->user()->can('create-authors'), 403);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'biography' => ['nullable', 'string', 'max:5000'],
            'birth_date' => ['nullable', 'date'],
            'death_date' => ['nullable', 'date', 'after_or_equal:birth_date'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'biography' => $this->biography,
            'birth_date' => $this->birth_date,
            'death_date' => $this->death_date,
            'nationality' => $this->nationality,
            'website' => $this->website,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $author = Author::findOrFail($this->authorId);
            $author->update($data);
            $this->dispatch('notify', message: 'Author updated successfully.', type: 'success');
            $this->redirect(route('catalog.authors'), navigate: true);
        } else {
            Author::create($data);
            $this->dispatch('notify', message: 'Author created successfully.', type: 'success');
            $this->redirect(route('catalog.authors'), navigate: true);
        }
    }

    public function render()
    {
        return view('catalog::livewire.author-form');
    }
}
