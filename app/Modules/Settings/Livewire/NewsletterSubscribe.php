<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\NewsletterSubscriber;
use Livewire\Component;

class NewsletterSubscribe extends Component
{
    public string $email = '';

    public string $theme = 'hero';

    public bool $subscribed = false;

    public ?string $error = null;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        try {
            NewsletterSubscriber::updateOrCreate(
                ['email' => $this->email],
                [
                    'name' => null,
                    'is_active' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]
            );

            $this->subscribed = true;
            $this->error = null;
            $this->email = '';
        } catch (\Throwable $e) {
            $this->error = 'Something went wrong. Please try again later.';
        }
    }

    public function render()
    {
        return view('settings::livewire.newsletter-subscribe');
    }
}
