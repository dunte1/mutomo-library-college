<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Circulation\Models\Reservation;
use App\Modules\Circulation\Services\ReservationService;
use Livewire\Component;
use Livewire\WithPagination;

class ReservationList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sort = 'reserved_at';

    public string $direction = 'desc';

    protected $queryString = ['search', 'status', 'sort', 'direction'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'direction']);
    }

    public function cancel(int $id): void
    {
        $reservation = Reservation::findOrFail($id);
        app(ReservationService::class)->cancelHold($reservation->id, auth()->user());
        $this->dispatch('notify', message: 'Reservation cancelled.', type: 'success');
    }

    public function cancelAsStaff(int $id): void
    {
        abort_unless(auth()->user()->can('manage-reservations'), 403);
        $reservation = Reservation::findOrFail($id);
        $reservation->update([
            'status' => Reservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'notes' => 'Cancelled by staff: '.auth()->user()->name,
        ]);
        $this->dispatch('notify', message: 'Reservation cancelled by staff.', type: 'success');
    }

    public function render()
    {
        $query = Reservation::with(['user', 'book'])
            ->when($this->search, fn ($q) => $q->whereHas('book', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%")));

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $allowedSorts = ['reserved_at', 'expires_at', 'status'];
        if (in_array($this->sort, $allowedSorts)) {
            $query->orderBy($this->sort, $this->direction === 'asc' ? 'asc' : 'desc');
        }

        $reservations = $query->paginate(15);

        return view('circulation::livewire.reservation-list', [
            'reservations' => $reservations,
            'stats' => [
                'pending' => Reservation::where('status', Reservation::STATUS_PENDING)->count(),
                'fulfilled' => Reservation::where('status', Reservation::STATUS_FULFILLED)->count(),
                'cancelled' => Reservation::where('status', Reservation::STATUS_CANCELLED)->count(),
                'expired' => Reservation::where('status', Reservation::STATUS_EXPIRED)->count(),
            ],
        ]);
    }
}
