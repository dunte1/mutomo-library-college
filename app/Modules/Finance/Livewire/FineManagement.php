<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Services\FineCalculationService;
use App\Modules\Finance\Services\FinanceService;
use Livewire\Component;
use Livewire\WithPagination;

class FineManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $type = '';

    public bool $showPayModal = false;

    public ?int $selectedFineId = null;

    public string $paymentMethod = 'cash';

    public ?string $reference = null;

    protected $queryString = ['search', 'status', 'type'];

    public function confirmPay(int $fineId): void
    {
        $this->selectedFineId = $fineId;
        $this->showPayModal = true;
        $this->dispatch('open-bottom-sheet', 'pay-fine');
    }

    public function pay()
    {
        $this->validate(['paymentMethod' => 'required|in:cash,mpesa,bank,card,cheque']);

        $fine = Fine::with('borrowRecord.user')->findOrFail($this->selectedFineId);

        app(FinanceService::class)->recordFinePayment(
            $fine,
            $this->paymentMethod,
            $this->reference
        );

        session()->flash('success', 'Fine payment recorded successfully.');
        $this->reset(['showPayModal', 'selectedFineId', 'paymentMethod', 'reference']);
        $this->dispatch('close-bottom-sheet', 'pay-fine');
    }

    public function waive(int $fineId)
    {
        $fine = Fine::findOrFail($fineId);
        app(FineCalculationService::class)->waiveFine($fine->id, 'Waived by '.(auth()->user()?->name ?? 'System'));

        session()->flash('success', 'Fine waived successfully.');
    }

    public function render()
    {
        $fines = Fine::with('borrowRecord.user', 'borrowRecord.bookCopy.book')
            ->when($this->search, fn ($q) => $q->whereHas('borrowRecord.user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('borrowRecord.bookCopy.book', fn ($q) => $q->where('title', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(15);

        return view('finance::livewire.fine-management', [
            'fines' => $fines,
        ])->layout('layouts.app');
    }
}
