<?php

namespace App\Modules\Finance\Livewire;

use App\Models\User;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Services\FinanceService;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $type = '';

    protected $queryString = ['search', 'status', 'type'];

    public function generateInvoice(int $userId, float $amount, string $type = 'fine'): void
    {
        $user = User::findOrFail($userId);

        app(FinanceService::class)->generateInvoice(
            $user,
            $amount,
            $type,
            "Invoice for {$type}"
        );

        $this->dispatch('notify', message: 'Invoice generated successfully.', type: 'success');
    }

    public function render()
    {
        $invoices = Invoice::with('user', 'issuer')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(15);

        return view('finance::livewire.invoice-list', [
            'invoices' => $invoices,
        ])->layout('layouts.app');
    }
}
