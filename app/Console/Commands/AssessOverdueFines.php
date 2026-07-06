<?php

namespace App\Console\Commands;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Services\FineCalculationService;
use Illuminate\Console\Command;

class AssessOverdueFines extends Command
{
    protected $signature = 'circulation:assess-overdue-fines';

    protected $description = 'Assess fines for all overdue borrow records';

    public function handle(): int
    {
        $overdueRecords = BorrowRecord::where('status', BorrowRecord::STATUS_ACTIVE)
            ->where('due_at', '<', now())
            ->whereNull('returned_at')
            ->get();

        $fineService = app(FineCalculationService::class);
        $assessed = 0;

        foreach ($overdueRecords as $record) {
            try {
                $fineService->assessOverdueFine($record);
                $assessed++;
            } catch (\Throwable $e) {
                $this->error("Failed to assess fine for borrow #{$record->id}: {$e->getMessage()}");
            }
        }

        $this->info("Assessed fines for {$assessed} overdue record(s).");

        return Command::SUCCESS;
    }
}
