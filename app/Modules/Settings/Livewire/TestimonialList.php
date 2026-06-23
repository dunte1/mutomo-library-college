<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\Testimonial;
use Livewire\Component;
use Livewire\WithPagination;

class TestimonialList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    protected $queryString = ['search', 'filterStatus'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();
        $this->dispatch('notify', message: 'Testimonial deleted successfully.', type: 'success');
    }

    public function approve(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['status' => 'approved']);
        $this->dispatch('notify', message: 'Testimonial approved.', type: 'success');
    }

    public function reject(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['status' => 'rejected']);
        $this->dispatch('notify', message: 'Testimonial rejected.', type: 'success');
    }

    public function moveUp(int $id): void
    {
        $item = Testimonial::findOrFail($id);
        $prev = Testimonial::where('sort_order', '<', $item->sort_order)
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
        $item = Testimonial::findOrFail($id);
        $next = Testimonial::where('sort_order', '>', $item->sort_order)
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
        $testimonials = Testimonial::when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->ordered()
            ->paginate(15);

        return view('settings::livewire.testimonial-list', [
            'testimonials' => $testimonials,
        ]);
    }
}
