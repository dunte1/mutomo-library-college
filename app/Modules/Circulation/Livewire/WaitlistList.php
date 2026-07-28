<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Circulation\Models\Reservation;
use Livewire\Component;
use Livewire\WithPagination;

class WaitlistList extends Component
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
        abort_unless(auth()->user()->can('manage-reservations'), 403);
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        $this->dispatch('notify', message: 'Waitlist entry removed.', type: 'success');
    }

    public function render()
    {
        $query = Reservation::with(['user', 'book'])
            ->where('status', Reservation::STATUS_PENDING)
            ->when($this->search, fn ($q) => $q->whereHas('book', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%")));

        $entries = $query->orderBy('reserved_at', 'desc')->paginate(15);

        return view('circulation::livewire.waitlist-list', [
            'entries' => $entries,
        ]);
    }
}
