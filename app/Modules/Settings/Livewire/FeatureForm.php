<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\Feature;
use Livewire\Component;

class FeatureForm extends Component
{
    public ?int $featureId = null;

    public string $title = '';

    public string $description = '';

    public ?string $icon = null;

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        if ($id) {
            $this->isEditing = true;
            $this->featureId = $id;
            $feature = Feature::findOrFail($id);

            $this->title = $feature->title;
            $this->description = $feature->description;
            $this->icon = $feature->icon;
            $this->sort_order = $feature->sort_order;
            $this->is_active = $feature->is_active;
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
            $feature = Feature::findOrFail($this->featureId);
            $feature->update($data);
            $this->dispatch('notify', message: 'Feature updated successfully.', type: 'success');
            $this->redirect(route('settings.features'), navigate: true);
        } else {
            Feature::create($data);
            $this->dispatch('notify', message: 'Feature created successfully.', type: 'success');
            $this->redirect(route('settings.features'), navigate: true);
        }
    }

    public function render()
    {
        return view('settings::livewire.feature-form');
    }
}
