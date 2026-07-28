<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use Livewire\Component;
use Livewire\WithPagination;

class DigitalAssetList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = '';

    public string $categoryId = '';

    public string $accessLevel = '';

    public string $sort = 'created_at';

    public string $direction = 'desc';

    protected $queryString = ['search', 'type', 'categoryId', 'accessLevel', 'sort', 'direction'];

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('delete-digital-assets'), 403);

        $asset = DigitalAsset::findOrFail($id);

        if ($asset->file_path && \Storage::disk('public')->exists($asset->file_path)) {
            \Storage::disk('public')->delete($asset->file_path);
        }
        if ($asset->cover_image && \Storage::disk('public')->exists($asset->cover_image)) {
            \Storage::disk('public')->delete($asset->cover_image);
        }

        $asset->delete();

        $this->dispatch('notify', type: 'success', message: 'Digital asset deleted successfully.');
    }

    public function render()
    {
        $allowedSortFields = ['title', 'created_at', 'file_size', 'publication_year', 'author'];
        $sort = in_array($this->sort, $allowedSortFields) ? $this->sort : 'created_at';
        $dir = in_array(strtolower($this->direction), ['asc', 'desc']) ? strtolower($this->direction) : 'desc';

        $assets = DigitalAsset::active()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('author', 'like', "%{$this->search}%");
            }))
            ->when($this->type, fn ($q) => $q->where('file_type', $this->type))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->accessLevel, fn ($q) => $q->where('access_level', $this->accessLevel))
            ->orderBy($sort, $dir)
            ->paginate(12);

        return view('digital-library::livewire.digital-asset-list', [
            'assets' => $assets,
            'categories' => DigitalAssetCategory::active()->get(),
            'types' => DigitalAsset::typeOptions(),
        ])->layout('layouts.app');
    }
}
