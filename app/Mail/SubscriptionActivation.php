<?php

namespace App\Mail;

use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Activated - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        $user = $this->subscription->user;
        $plan = $this->subscription->plan;

        return new Content(
            markdown: 'emails.subscription-activation',
            with: [
                'name' => $user->name,
                'planName' => $plan->name,
                'amount' => number_format($plan->price, 2),
                'currency' => $plan->currency,
                'billingCycle' => $plan->billing_cycle,
                'startDate' => $this->subscription->start_date->format('d M Y'),
                'endDate' => $this->subscription->end_date->format('d M Y'),
                'autoRenew' => $this->subscription->auto_renew ? 'Yes' : 'No',
                'libraryName' => config('app.name'),
                'libraryPhone' => config('app.library_phone', ''),
                'libraryEmail' => config('app.library_email', ''),
                'loginUrl' => route('login'),
                'subscriptionUrl' => route('subscriptions.my'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
