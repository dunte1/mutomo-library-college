<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\Feature;
use Livewire\Component;
use Livewire\WithPagination;

class FeatureList extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $feature = Feature::findOrFail($id);
        $feature->delete();
        $this->dispatch('notify', message: 'Feature deleted successfully.', type: 'success');
    }

    public function moveUp(int $id): void
    {
        $feature = Feature::findOrFail($id);
        $prev = Feature::where('sort_order', '<', $feature->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
        if ($prev) {
            $temp = $feature->sort_order;
            $feature->update(['sort_order' => $prev->sort_order]);
            $prev->update(['sort_order' => $temp]);
        }
    }

    public function moveDown(int $id): void
    {
        $feature = Feature::findOrFail($id);
        $next = Feature::where('sort_order', '>', $feature->sort_order)
            ->orderBy('sort_order')
            ->first();
        if ($next) {
            $temp = $feature->sort_order;
            $feature->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $temp]);
        }
    }

    public function render()
    {
        $features = Feature::when($this->search, fn ($q) => $q->search($this->search))
            ->ordered()
            ->paginate(15);

        return view('settings::livewire.feature-list', [
            'features' => $features,
        ]);
    }
}
