<?php

namespace App\Modules\Circulation\Livewire;

use App\Models\User;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Services\BorrowingService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class IssueBook extends Component
{
    public string $searchUser = '';

    public string $searchBook = '';

    public ?int $selectedUserId = null;

    public ?int $selectedCopyId = null;

    public $selectedUser = null;

    public $selectedCopy = null;

    public string $barcode = '';

    public string $message = '';

    public string $messageType = '';

    protected $rules = [
        'selectedUserId' => 'required|exists:users,id',
        'selectedCopyId' => 'required|exists:book_copies,id',
    ];

    public function searchByBarcode(): void
    {
        $copy = BookCopy::with('book')
            ->where('barcode', $this->barcode)
            ->where('status', 'available')
            ->first();

        if ($copy) {
            $this->selectedCopyId = $copy->id;
            $this->selectedCopy = $copy;
            $this->message = '';
        } else {
            $this->message = 'Book not found or not available.';
            $this->messageType = 'error';
            $this->selectedCopyId = null;
            $this->selectedCopy = null;
        }
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->selectedUser = User::with('department')->find($userId);
        $this->searchUser = '';
    }

    public function selectCopy(int $copyId): void
    {
        $this->selectedCopyId = $copyId;
        $this->selectedCopy = BookCopy::with('book')->find($copyId);
        $this->searchBook = '';
    }

    public function issue(): void
    {
        $this->authorize('borrow-books');
        $this->validate();

        try {
            $service = app(BorrowingService::class);
            $record = $service->issueBook($this->selectedUserId, $this->selectedCopyId);

            $this->dispatch('notify', type: 'success', message: 'Book issued successfully!');

            $this->reset(['selectedUserId', 'selectedCopyId', 'selectedUser', 'selectedCopy', 'barcode', 'searchUser', 'searchBook']);
        } catch (\RuntimeException $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'error';
        } catch (\Throwable $e) {
            Log::error('Issue book failed', ['error' => $e->getMessage()]);
            $this->message = 'An unexpected error occurred.';
            $this->messageType = 'error';
        }
    }

    public function resetSelection(): void
    {
        $this->reset(['selectedUserId', 'selectedCopyId', 'selectedUser', 'selectedCopy', 'barcode', 'searchUser', 'searchBook', 'message', 'messageType']);
    }

    public function render()
    {
        $users = [];
        $copies = [];

        if (strlen($this->searchUser) >= 2) {
            $users = User::with('department')
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->searchUser}%")
                        ->orWhere('email', 'like', "%{$this->searchUser}%")
                        ->orWhere('admission_number', 'like', "%{$this->searchUser}%")
                        ->orWhere('employee_id', 'like', "%{$this->searchUser}%");
                })
                ->active()
                ->limit(10)
                ->get();
        }

        if (strlen($this->searchBook) >= 2) {
            $copies = BookCopy::with('book.authors')
                ->where('status', 'available')
                ->whereHas('book', function ($q) {
                    $q->where('title', 'like', "%{$this->searchBook}%")
                        ->orWhere('isbn', 'like', "%{$this->searchBook}%");
                })
                ->limit(10)
                ->get();
        }

        return view('circulation::livewire.issue-book', [
            'searchResults' => $users,
            'copyResults' => $copies,
        ]);
    }
}
