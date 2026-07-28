<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Services\BorrowingService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ReturnBook extends Component
{
    public string $barcode = '';

    public $record = null;

    public string $condition = 'good';

    public string $message = '';

    public string $messageType = '';

    public bool $showConfirm = false;

    public function searchByBarcode(): void
    {
        $this->record = BorrowRecord::with(['user', 'bookCopy.book'])
            ->whereHas('bookCopy', function ($q) {
                $q->where('barcode', $this->barcode);
            })
            ->active()
            ->latest()
            ->first();

        if (! $this->record) {
            $this->message = 'No active borrow found for this barcode.';
            $this->messageType = 'error';
            $this->showConfirm = false;
        } else {
            $this->message = '';
            $this->showConfirm = true;
        }
    }

    public function confirmReturn(): void
    {
        $this->authorize('return-books');
        try {
            $service = app(BorrowingService::class);
            $record = $service->returnBook($this->record->id, $this->condition);

            $this->dispatch('notify', type: 'success', message: 'Book returned successfully!');

            $this->reset(['barcode', 'record', 'condition', 'message', 'messageType', 'showConfirm']);
        } catch (\RuntimeException $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'error';
        } catch (\Throwable $e) {
            Log::error('Return book failed', ['error' => $e->getMessage()]);
            $this->message = 'An unexpected error occurred.';
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('circulation::livewire.return-book');
    }
}
