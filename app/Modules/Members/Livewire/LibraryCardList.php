<?php

namespace App\Modules\Members\Livewire;

use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Services\LibraryCardService;
use Livewire\Component;
use Livewire\WithPagination;

class LibraryCardList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

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
        $this->reset(['search', 'status']);
    }

    public function render()
    {
        $query = LibraryCard::with('member', 'issuer')
            ->when($this->search, function ($q) {
                $q->whereHas('member', function ($mq) {
                    $mq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('member_id', 'like', "%{$this->search}%");
                })->orWhere('card_number', 'like', "%{$this->search}%");
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            })
            ->orderByDesc('created_at');

        $cards = $query->paginate(15);
        $stats = app(LibraryCardService::class)->getCardStats();

        return view('members::livewire.library-card-list', [
            'cards' => $cards,
            'stats' => $stats,
        ]);
    }
}
