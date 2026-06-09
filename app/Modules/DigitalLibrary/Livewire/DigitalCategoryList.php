<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use Livewire\Component;
use Livewire\WithPagination;

class DigitalCategoryList extends Component
{
    use WithPagination;

    public string $name = '';
    public string $description = '';
    public bool $isActive = true;
    public ?int $editingId = null;
    public bool $showForm = false;

    public function create(): void
    {
        $this->authorize('manage-digital-categories');
        $this->reset(['name', 'description', 'editingId']);
        $this->isActive = true;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('manage-digital-categories');
        $category = DigitalAssetCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->isActive = $category->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('manage-digital-categories');
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'isActive' => 'boolean',
        ]);

        try {
            if ($this->editingId) {
                $category = DigitalAssetCategory::findOrFail($this->editingId);
                $category->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'is_active' => $this->isActive,
                ]);
                $this->dispatch('notify', type: 'success', message: 'Category updated successfully.');
            } else {
                DigitalAssetCategory::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'is_active' => $this->isActive,
                ]);
                $this->dispatch('notify', type: 'success', message: 'Category created successfully.');
            }

            $this->showForm = false;
            $this->reset(['name', 'description', 'editingId']);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to save category.');
        }
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->reset(['name', 'description', 'editingId']);
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('manage-digital-categories');
        try {
            $category = DigitalAssetCategory::findOrFail($id);
            $category->update(['is_active' => !$category->is_active]);
            $this->dispatch('notify', type: 'success', message: 'Category status updated.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to update category status.');
        }
    }

    public function render()
    {
        $categories = DigitalAssetCategory::withCount('assets')
            ->orderBy('name')
            ->paginate(15);

        return view('digital-library::livewire.digital-category-list', [
            'categories' => $categories,
        ]);
    }
}
