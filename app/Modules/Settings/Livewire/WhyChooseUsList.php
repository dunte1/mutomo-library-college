<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\WhyChooseUs;
use Livewire\Component;
use Livewire\WithPagination;

class WhyChooseUsList extends Component
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
        $item = WhyChooseUs::findOrFail($id);
        $item->delete();
        $this->dispatch('notify', message: 'Item deleted successfully.', type: 'success');
    }

    public function moveUp(int $id): void
    {
        $item = WhyChooseUs::findOrFail($id);
        $prev = WhyChooseUs::where('sort_order', '<', $item->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
        if ($prev) {
            $temp = $item->sort_order;
            $item->update(['sort_order' => $prev->sort_order]);
            $prev->update(['sort_order' => $temp]);
        }
    }

    public function moveDown(int $id): void
    {
        $item = WhyChooseUs::findOrFail($id);
        $next = WhyChooseUs::where('sort_order', '>', $item->sort_order)
            ->orderBy('sort_order')
            ->first();
        if ($next) {
            $temp = $item->sort_order;
            $item->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $temp]);
        }
    }

    public function render()
    {
        $items = WhyChooseUs::when($this->search, fn ($q) => $q->search($this->search))
            ->ordered()
            ->paginate(15);

        return view('settings::livewire.why-choose-us-list', [
            'items' => $items,
        ]);
    }
}
