<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Finance\Models\Transaction;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = '';
    public string $paymentMethod = '';
    public string $status = '';
    public string $sort = 'created_at';
    public string $direction = 'desc';

    protected $queryString = ['search', 'type', 'paymentMethod', 'status', 'sort', 'direction'];

    public function exportCsv()
    {
        $transactions = Transaction::with('user')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('transaction_number', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->paymentMethod, fn ($q) => $q->where('payment_method', $this->paymentMethod))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="transactions-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Transaction #', 'User', 'Type', 'Payment Method', 'Amount', 'Reference', 'Date', 'Status']);

            foreach ($transactions as $txn) {
                fputcsv($file, [
                    $txn->transaction_number,
                    $txn->user?->name ?? 'N/A',
                    $txn->type,
                    $txn->payment_method ?? '-',
                    number_format($txn->amount, 2),
                    $txn->reference ?? '-',
                    $txn->paid_at?->format('Y-m-d') ?? $txn->created_at->format('Y-m-d'),
                    $txn->status,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function render()
    {
        $allowedSortFields = ['created_at', 'amount', 'transaction_number', 'status', 'paid_at'];
        $sort = in_array($this->sort, $allowedSortFields) ? $this->sort : 'created_at';
        $dir = in_array(strtolower($this->direction), ['asc', 'desc']) ? strtolower($this->direction) : 'desc';

        $transactions = Transaction::with('user')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('transaction_number', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->paymentMethod, fn ($q) => $q->where('payment_method', $this->paymentMethod))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy($sort, $dir)
            ->paginate(15);

        return view('finance::livewire.transaction-list', [
            'transactions' => $transactions,
            'types' => Transaction::typeOptions(),
            'methods' => Transaction::paymentMethodOptions(),
        ])->layout('layouts.app');
    }
}
