<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Circulation\Models\Fine;
use App\Modules\Finance\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RefundManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showRefundModal = false;

    public ?int $selectedTransactionId = null;

    public ?string $refundReason = null;

    protected $queryString = ['search'];

    public function confirmRefund(int $transactionId): void
    {
        $this->selectedTransactionId = $transactionId;
        $this->showRefundModal = true;
        $this->refundReason = null;
        $this->dispatch('open-bottom-sheet', 'refund');
    }

    public function processRefund(): void
    {
        $this->validate([
            'selectedTransactionId' => 'required|integer',
            'refundReason' => 'required|string|min:5',
        ]);

        $transaction = Transaction::with('fine')->findOrFail($this->selectedTransactionId);

        if ($transaction->status !== 'completed') {
            $this->dispatch('notify', message: 'Only completed transactions can be refunded.', type: 'error');

            return;
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'refunded']);

            if ($transaction->fine) {
                $transaction->fine->update([
                    'status' => Fine::STATUS_PENDING,
                    'paid_amount' => 0,
                    'paid_at' => null,
                ]);
            }

            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->log("Refunded transaction {$transaction->transaction_number}: {$this->refundReason}");
        });

        $this->dispatch('notify', message: 'Refund processed successfully.', type: 'success');
        $this->reset(['showRefundModal', 'selectedTransactionId', 'refundReason']);
        $this->dispatch('close-bottom-sheet', 'refund');
    }

    public function render()
    {
        $transactions = Transaction::with('user', 'fine')
            ->where('status', 'completed')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('transaction_number', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->latest()
            ->paginate(15);

        return view('finance::livewire.refund-management', [
            'transactions' => $transactions,
        ])->layout('layouts.app');
    }
}
