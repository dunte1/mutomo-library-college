<?php

namespace App\Modules\Finance\Livewire;

use App\Models\User;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Finance\Services\FinanceService;
use Livewire\Component;

class CollectPayments extends Component
{
    public string $search = '';

    public ?int $selectedUserId = null;

    public ?string $selectedUserName = null;

    public array $outstandingFines = [];

    public float $totalAmount = 0;

    public float $amount = 0;

    public string $paymentMethod = 'cash';

    public ?string $reference = null;

    protected $queryString = ['search'];

    public function updatedSearch(): void
    {
        $this->selectedUserId = null;
        $this->selectedUserName = null;
        $this->outstandingFines = [];
        $this->totalAmount = 0;
        $this->amount = 0;
    }

    public function selectUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->selectedUserId = $user->id;
        $this->selectedUserName = $user->name;
        $this->search = '';

        $this->outstandingFines = Fine::with('borrowRecord.bookCopy.book')
            ->where('user_id', $this->selectedUserId)
            ->where('status', Fine::STATUS_PENDING)
            ->get()
            ->toArray();

        $this->totalAmount = array_sum(array_column($this->outstandingFines, 'amount'));
        $this->amount = $this->totalAmount;
    }

    public function payAll(): void
    {
        $this->authorize('collect-payments');
        $this->validate([
            'selectedUserId' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|in:cash,mpesa,bank,card,cheque',
        ]);

        if (empty($this->outstandingFines)) {
            $this->dispatch('notify', message: 'No outstanding fines to collect.', type: 'error');

            return;
        }

        $fines = Fine::where('user_id', $this->selectedUserId)
            ->where('status', Fine::STATUS_PENDING)
            ->get();

        foreach ($fines as $fine) {
            app(FinanceService::class)->recordFinePayment(
                $fine,
                $this->paymentMethod,
                $this->reference
            );
        }

        $this->dispatch('notify', message: 'Payment collected successfully.', type: 'success');
        $this->reset(['selectedUserId', 'selectedUserName', 'outstandingFines', 'totalAmount', 'amount', 'paymentMethod', 'reference']);
    }

    public function render()
    {
        $users = collect();
        if (strlen($this->search) >= 2) {
            $users = User::where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('admission_number', 'like', "%{$this->search}%");
            })
                ->active()
                ->limit(10)
                ->get();
        }

        return view('finance::livewire.collect-payments', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}
