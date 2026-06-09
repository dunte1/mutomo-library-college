<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\WhyChooseUs;
use Livewire\Component;

class WhyChooseUsForm extends Component
{
    public ?int $itemId = null;
    public string $title = '';
    public string $description = '';
    public ?string $icon = null;
    public int $sort_order = 0;
    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->itemId = $id;
            $item = WhyChooseUs::findOrFail($id);

            $this->title = $item->title;
            $this->description = $item->description;
            $this->icon = $item->icon;
            $this->sort_order = $item->sort_order;
            $this->is_active = $item->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $item = WhyChooseUs::findOrFail($this->itemId);
            $item->update($data);
            $this->dispatch('notify', message: 'Item updated successfully.', type: 'success');
            $this->redirect(route('settings.why-choose-us'), navigate: true);
        } else {
            WhyChooseUs::create($data);
            $this->dispatch('notify', message: 'Item created successfully.', type: 'success');
            $this->redirect(route('settings.why-choose-us'), navigate: true);
        }
    }

    public function render()
    {
        return view('settings::livewire.why-choose-us-form');
    }
}
