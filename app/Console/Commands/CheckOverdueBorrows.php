<?php

namespace App\Console\Commands;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Services\FineCalculationService;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

class CheckOverdueBorrows extends Command
{
    protected $signature = 'circulation:check-overdue';

    protected $description = 'Mark overdue borrows and assess fines';

    public function handle(FineCalculationService $fineService, NotificationService $notificationService): int
    {
        $overdueRecords = BorrowRecord::with('user')
            ->where('status', BorrowRecord::STATUS_ACTIVE)
            ->where('due_at', '<', now())
            ->get();

        $count = 0;
        foreach ($overdueRecords as $record) {
            $record->update(['status' => BorrowRecord::STATUS_OVERDUE]);

            try {
                $fine = $fineService->assessOverdueFine($record);

                $notificationService->sendOverdueNotice(
                    $record->user,
                    $record->bookCopy?->book?->title ?? 'Unknown Book',
                    $record->daysOverdue(),
                );
            } catch (\Throwable $e) {
                $this->error("Failed processing borrow #{$record->id}: {$e->getMessage()}");
            }

            $count++;
        }

        $this->info("Processed {$count} overdue borrow record(s).");

        return Command::SUCCESS;
    }
}
