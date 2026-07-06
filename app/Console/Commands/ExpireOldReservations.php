<?php

namespace App\Console\Commands;

use App\Modules\Circulation\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireOldReservations extends Command
{
    protected $signature = 'circulation:expire-reservations';

    protected $description = 'Expire old holds that have passed their expiration date';

    public function handle(): int
    {
        $expired = app(ReservationService::class)->expireOldHolds();

        $this->info("Expired {$expired} old reservation(s).");

        return Command::SUCCESS;
    }
}
