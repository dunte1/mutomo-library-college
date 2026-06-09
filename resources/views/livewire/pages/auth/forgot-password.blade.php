<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="text-center lg:text-left mb-8">
        <h2 class="text-2xl font-bold text-surface-900 tracking-tight">Reset password</h2>
        <p class="text-sm text-surface-500 mt-1.5">Enter your email and we'll send you a reset link</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus placeholder="you@ollmchs.ac.ke" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            <span wire:loading.remove wire:target="sendPasswordResetLink">Send reset link</span>
            <span wire:loading wire:target="sendPasswordResetLink" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Sending...
            </span>
        </x-primary-button>
    </form>

    <p class="text-center text-sm text-surface-500 mt-6">
        Remember your password?
        <a href="{{ route('login') }}" wire:navigate class="text-primary-600 hover:text-primary-700 font-medium transition-colors">Sign in</a>
    </p>
</div>
