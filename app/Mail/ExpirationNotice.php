<?php

namespace App\Mail;

use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpirationNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public string $noticeType, // 'expiring_soon' or 'expired'
        public ?int $daysUntilExpiry = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->noticeType === 'expired'
            ? 'Membership Expired - ' . config('app.name')
            : 'Membership Expiring Soon - ' . config('app.name');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $user = $this->subscription->user;
        $plan = $this->subscription->plan;

        return new Content(
            markdown: 'emails.expiration-notice',
            with: [
                'name' => $user->name,
                'planName' => $plan->name,
                'noticeType' => $this->noticeType,
                'daysUntilExpiry' => $this->daysUntilExpiry,
                'endDate' => $this->subscription->end_date->format('d M Y'),
                'libraryName' => config('app.name'),
                'libraryPhone' => config('app.library_phone', ''),
                'libraryEmail' => config('app.library_email', ''),
                'plansUrl' => route('subscriptions.plans'),
                'subscriptionUrl' => route('subscriptions.my'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
