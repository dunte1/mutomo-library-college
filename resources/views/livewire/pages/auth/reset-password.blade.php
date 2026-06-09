<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <div class="text-center lg:text-left mb-8">
        <h2 class="text-2xl font-bold text-surface-900 tracking-tight">Set new password</h2>
        <p class="text-sm text-surface-500 mt-1.5">Choose a strong password for your account</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="resetPassword" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="you@ollmchs.ac.ke" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1.5 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            <span wire:loading.remove wire:target="resetPassword">Reset password</span>
            <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Resetting...
            </span>
        </x-primary-button>
    </form>

    <p class="text-center text-sm text-surface-500 mt-6">
        <a href="{{ route('login') }}" wire:navigate class="text-primary-600 hover:text-primary-700 font-medium transition-colors">Back to sign in</a>
    </p>
</div>
