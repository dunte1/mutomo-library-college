<?php

namespace App\Console\Commands;

use App\Modules\Communication\Services\MessagingService;
use Illuminate\Console\Command;

class SendScheduledMessages extends Command
{
    protected $signature = 'schedule:scheduled-messages';

    protected $description = 'Send messages that are scheduled for delivery';

    public function handle(MessagingService $messagingService): int
    {
        $count = $messagingService->sendScheduledMessages();

        $this->info("Sent {$count} scheduled message(s).");

        return Command::SUCCESS;
    }
}
