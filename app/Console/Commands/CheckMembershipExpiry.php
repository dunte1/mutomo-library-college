<?php

namespace App\Console\Commands;

use App\Modules\Members\Models\Member;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

class CheckMembershipExpiry extends Command
{
    protected $signature = 'members:check-expiry';

    protected $description = 'Expire memberships past their expiry date and warn those expiring soon';

    public function handle(): int
    {
        $notificationService = app(NotificationService::class);

        $expired = Member::where('status', Member::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $member) {
            $member->update(['status' => Member::STATUS_EXPIRED]);
            if ($member->user) {
                $notificationService->send(
                    $member->user,
                    'membership_expired',
                    'Membership Expired',
                    'Your library membership has expired. Please renew to continue borrowing.',
                    'alert-triangle',
                    route('profile'),
                );
            }
        }

        $warningDate = now()->addDays(7);
        $expiringSoon = Member::where('status', Member::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), $warningDate])
            ->get();

        foreach ($expiringSoon as $member) {
            if ($member->user) {
                $daysLeft = now()->diffInDays($member->expires_at);
                $notificationService->send(
                    $member->user,
                    'membership_expiring',
                    'Membership Expiring Soon',
                    "Your library membership expires in {$daysLeft} day(s). Please renew to avoid interruption.",
                    'clock',
                    route('profile'),
                );
            }
        }

        $this->info("Expired {$expired->count()} membership(s), warned {$expiringSoon->count()} expiring soon.");

        return Command::SUCCESS;
    }
}
