<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Services\FinanceService;
use Livewire\Component;
use Livewire\WithPagination;

class ReceiptView extends Component
{
    use WithPagination;

    public ?Transaction $transaction = null;
    public bool $showInvoice = false;
    public string $search = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->transaction = Transaction::with(['user', 'fine.borrowRecord.bookCopy.book', 'receipt', 'invoice'])
                ->findOrFail($id);
        }
    }

    public function viewReceipt(int $id): void
    {
        $this->transaction = Transaction::with(['user', 'fine.borrowRecord.bookCopy.book', 'receipt', 'invoice'])
            ->findOrFail($id);
    }

    public function toggleView(): void
    {
        $this->showInvoice = !$this->showInvoice;
    }

    public function render()
    {
        if ($this->transaction) {
            return view('finance::livewire.receipt-view')
                ->layout('layouts.app');
        }

        $receipts = Transaction::with(['user'])
            ->whereNotNull('receipt_number')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('receipt_number', 'like', "%{$this->search}%")
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('finance::livewire.receipt-view', [
            'receipts' => $receipts,
        ])->layout('layouts.app');
    }
}
