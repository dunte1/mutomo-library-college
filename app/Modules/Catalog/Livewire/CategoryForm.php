<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;

class CategoryForm extends Component
{
    public ?int $categoryId = null;

    public string $name = '';

    public ?string $description = null;

    public ?int $parent_id = null;

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->isEditing = true;
            $this->categoryId = $id;
            $category = Category::findOrFail($id);

            $this->name = $category->name;
            $this->description = $category->description;
            $this->parent_id = $category->parent_id;
            $this->sort_order = $category->sort_order;
            $this->is_active = $category->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $category = Category::findOrFail($this->categoryId);
            if ($this->parent_id == $this->categoryId) {
                session()->flash('error', 'A category cannot be its own parent.');

                return;
            }
            $category->update($data);
            session()->flash('success', 'Category updated successfully.');
            $this->redirect(route('catalog.categories'), navigate: true);
        } else {
            Category::create($data);
            session()->flash('success', 'Category created successfully.');
            $this->redirect(route('catalog.categories'), navigate: true);
        }
    }

    public function render()
    {
        $allCategories = Category::active()
            ->when($this->categoryId, fn ($q) => $q->where('id', '!=', $this->categoryId))
            ->orderBy('name')
            ->get();

        return view('catalog::livewire.category-form', [
            'allCategories' => $allCategories,
        ]);
    }
}
