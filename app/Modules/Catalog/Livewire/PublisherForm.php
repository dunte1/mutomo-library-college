<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Publisher;
use Illuminate\Support\Str;
use Livewire\Component;

class PublisherForm extends Component
{
    public ?int $publisherId = null;
    public string $name = '';
    public ?string $address = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $website = null;
    public ?string $description = null;
    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->publisherId = $id;
            $publisher = Publisher::findOrFail($id);

            $this->name = $publisher->name;
            $this->address = $publisher->address;
            $this->phone = $publisher->phone;
            $this->email = $publisher->email;
            $this->website = $publisher->website;
            $this->description = $publisher->description;
            $this->is_active = $publisher->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $publisher = Publisher::findOrFail($this->publisherId);
            $publisher->update($data);
            $this->dispatch('notify', message: 'Publisher updated successfully.', type: 'success');
            $this->redirect(route('catalog.publishers'), navigate: true);
        } else {
            Publisher::create($data);
            $this->dispatch('notify', message: 'Publisher created successfully.', type: 'success');
            $this->redirect(route('catalog.publishers'), navigate: true);
        }
    }

    public function render()
    {
        return view('catalog::livewire.publisher-form');
    }
}
