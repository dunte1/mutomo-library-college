<?php

namespace App\Jobs;

use App\Modules\Circulation\Models\BorrowRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckOverdueBorrowsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        BorrowRecord::where('status', BorrowRecord::STATUS_ACTIVE)
            ->where('due_at', '<', now())
            ->update(['status' => BorrowRecord::STATUS_OVERDUE]);
    }
}
