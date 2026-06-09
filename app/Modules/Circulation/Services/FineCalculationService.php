<?php

namespace App\Modules\Circulation\Services;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Shared\Helpers\AuditHelper;

class FineCalculationService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected float $dailyRate = 50.00,
        protected float $lostBookRate = 1500.00,
        protected float $damageRate = 500.00,
    ) {}

    public function assessOverdueFine(BorrowRecord $record): Fine
    {
        $existing = Fine::where('borrow_record_id', $record->id)
            ->where('status', Fine::STATUS_PENDING)
            ->first();

        if ($existing) {
            return $existing;
        }

        $daysOverdue = $record->daysOverdue();
        $amount = $daysOverdue * $this->dailyRate;

        $fine = Fine::create([
            'borrow_record_id' => $record->id,
            'user_id' => $record->user_id,
            'amount' => $amount,
            'status' => Fine::STATUS_PENDING,
            'reason' => "Overdue fine ({$daysOverdue} days at KES {$this->dailyRate}/day)",
            'assessed_at' => now(),
            'assessed_by' => auth()->id(),
        ]);

        AuditHelper::log('fine-assessed', 'circulation', [
            'fine_id' => $fine->id,
            'borrow_id' => $record->id,
            'amount' => $amount,
            'reason' => 'overdue',
        ]);

        $this->notificationService->sendFineAssessed(
            $record->user,
            "Overdue fine ({$daysOverdue} days at KES {$this->dailyRate}/day)",
            $amount,
        );

        return $fine;
    }

    public function assessLostBookFine(BorrowRecord $record): Fine
    {
        $existing = Fine::where('borrow_record_id', $record->id)
            ->where('status', Fine::STATUS_PENDING)
            ->first();

        if ($existing) {
            return $existing;
        }

        $amount = $this->lostBookRate + ($record->daysOverdue() * $this->dailyRate);

        $fine = Fine::create([
            'borrow_record_id' => $record->id,
            'user_id' => $record->user_id,
            'amount' => $amount,
            'status' => Fine::STATUS_PENDING,
            'reason' => 'Lost book replacement fee',
            'assessed_at' => now(),
            'assessed_by' => auth()->id(),
        ]);

        AuditHelper::log('fine-assessed-lost', 'circulation', [
            'fine_id' => $fine->id,
            'amount' => $amount,
            'reason' => 'lost',
        ]);

        $this->notificationService->sendFineAssessed(
            $record->user,
            "Lost book replacement fee for borrow #{$record->id}",
            $amount,
        );

        return $fine;
    }

    public function assessDamageFine(BorrowRecord $record): Fine
    {
        $existing = Fine::where('borrow_record_id', $record->id)
            ->where('status', Fine::STATUS_PENDING)
            ->first();

        if ($existing) {
            return $existing;
        }

        $fine = Fine::create([
            'borrow_record_id' => $record->id,
            'user_id' => $record->user_id,
            'amount' => $this->damageRate,
            'status' => Fine::STATUS_PENDING,
            'reason' => 'Damaged book fine',
            'assessed_at' => now(),
            'assessed_by' => auth()->id(),
        ]);

        AuditHelper::log('fine-assessed-damage', 'circulation', [
            'fine_id' => $fine->id,
            'amount' => $this->damageRate,
            'reason' => 'damaged',
        ]);

        $this->notificationService->sendFineAssessed(
            $record->user,
            "Damaged book fine for borrow #{$record->id}",
            $this->damageRate,
        );

        return $fine;
    }

    public function waiveFine(int $fineId, string $reason): Fine
    {
        $fine = Fine::findOrFail($fineId);

        if ($fine->status === Fine::STATUS_PAID || $fine->status === Fine::STATUS_WAIVED) {
            throw new \RuntimeException('Fine has already been ' . $fine->status . '.');
        }

        $waivedAmount = $fine->amount - $fine->paid_amount;

        $fine->update([
            'status' => Fine::STATUS_WAIVED,
            'waived_amount' => $waivedAmount,
            'waived_at' => now(),
            'waived_by' => auth()->id(),
            'notes' => $reason,
        ]);

        AuditHelper::log('fine-waived', 'circulation', [
            'fine_id' => $fine->id,
            'waived_amount' => $fine->waived_amount,
        ]);

        return $fine;
    }

    public function payFine(int $fineId, float $amount): Fine
    {
        $fine = Fine::findOrFail($fineId);

        $newPaid = $fine->paid_amount + $amount;

        $updateData = [
            'paid_amount' => $newPaid,
        ];

        if ($newPaid >= $fine->amount) {
            $updateData['status'] = Fine::STATUS_PAID;
            $updateData['paid_at'] = now();
        }

        $fine->update($updateData);

        AuditHelper::log('fine-payment', 'circulation', [
            'fine_id' => $fine->id,
            'amount' => $amount,
            'remaining' => $fine->amount - $newPaid,
        ]);

        return $fine->fresh();
    }

    public function getUserOutstandingFines(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Fine::byUser($userId)->pending()->get();
    }

    public function getUserTotalOutstanding(int $userId): float
    {
        return Fine::byUser($userId)->pending()->get()
            ->sum(fn($fine) => $fine->outstanding_balance);
    }
}
