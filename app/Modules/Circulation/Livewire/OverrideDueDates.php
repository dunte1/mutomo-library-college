<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Circulation\Models\BorrowRecord;
use Livewire\Component;
use Livewire\WithPagination;

class OverrideDueDates extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedBorrowId = null;

    public string $newDueDate = '';

    public string $reason = '';

    protected $rules = [
        'selectedBorrowId' => 'required|exists:borrow_records,id',
        'newDueDate' => 'required|date|after:today',
        'reason' => 'required|string|max:500',
    ];

    protected $messages = [
        'newDueDate.after' => 'The new due date must be after today.',
        'reason.required' => 'Please provide a reason for the override.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectBorrow(int $id): void
    {
        $this->selectedBorrowId = $id;
        $this->search = '';
    }

    public function override(): void
    {
        $this->validate();

        $record = BorrowRecord::with(['user', 'bookCopy.book'])->findOrFail($this->selectedBorrowId);

        $record->update([
            'due_at' => $this->newDueDate,
            'notes' => trim(($record->notes ?? '')."\n[Override] ".$this->reason.' (by '.auth()->user()->name.' on '.now()->format('Y-m-d H:i').')'),
        ]);

        $this->dispatch('notify', message: 'Due date overridden successfully.', type: 'success');

        $this->reset(['selectedBorrowId', 'newDueDate', 'reason', 'search']);
    }

    public function render()
    {
        $borrows = BorrowRecord::with(['user', 'bookCopy.book'])
            ->active()
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
                ->orWhereHas('bookCopy.book', fn ($q) => $q->where('title', 'like', "%{$this->search}%")))
            ->orderBy('due_at', 'asc')
            ->paginate(10);

        return view('circulation::livewire.override-due-dates', [
            'borrows' => $borrows,
        ]);
    }
}
