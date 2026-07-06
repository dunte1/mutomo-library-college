<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class WelcomeCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetLink;

    public function __construct(
        public User $user,
    ) {
        $token = Password::createToken($user);
        $this->resetLink = route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to '.config('app.name').' – Set Your Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome-credentials',
            with: [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'resetLink' => $this->resetLink,
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
