<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Services\FineCalculationService;
use Livewire\Component;
use Livewire\WithPagination;

class FineManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public int $fineIdToWaive = 0;

    public string $waiveReason = '';

    public function payFine(int $fineId): void
    {
        $this->authorize('manage-fines');

        try {
            $service = app(FineCalculationService::class);
            $fine = Fine::findOrFail($fineId);
            $service->payFine($fineId, $fine->outstanding_balance);
            $this->dispatch('notify', type: 'success', message: 'Fine paid successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to pay fine: '.$e->getMessage());
        }
    }

    public function confirmWaive(int $fineId): void
    {
        $this->fineIdToWaive = $fineId;
        $this->waiveReason = '';
    }

    public function waiveFine(): void
    {
        $this->authorize('manage-fines');
        $this->validate(['waiveReason' => 'required|string|max:255']);

        try {
            app(FineCalculationService::class)->waiveFine($this->fineIdToWaive, $this->waiveReason);
            $this->fineIdToWaive = 0;
            $this->waiveReason = '';
            $this->dispatch('notify', type: 'success', message: 'Fine waived successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to waive fine: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = Fine::with(['user', 'borrowRecord.bookCopy.book']);

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
        }

        $fines = $query->latest('assessed_at')->paginate(15);

        return view('circulation::livewire.fine-management', [
            'fines' => $fines,
        ]);
    }
}
