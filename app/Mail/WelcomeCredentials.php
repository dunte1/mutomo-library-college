<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' – Your Login Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome-credentials',
            with: [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'password' => $this->plainPassword,
                'loginUrl' => route('login'),
                'libraryName' => config('app.name'),
                'libraryPhone' => config('app.library_phone', ''),
                'libraryEmail' => config('app.library_email', ''),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
