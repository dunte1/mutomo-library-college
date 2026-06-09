<?php

namespace App\Modules\Circulation\Livewire;

use App\Modules\Circulation\Services\BorrowingService;
use Livewire\Component;

class PatronActions extends Component
{
    public function renew(int $borrowId): void
    {
        try {
            app(BorrowingService::class)->renewBook($borrowId);
            $this->dispatch('notify', message: 'Book renewed successfully!', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Patron action failed', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: 'An unexpected error occurred.', type: 'error');
        }
    }

    public function render()
    {
        return view('circulation::livewire.patron-actions');
    }
}
