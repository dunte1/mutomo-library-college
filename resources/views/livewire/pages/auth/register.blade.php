<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $validated['is_active'] = false;

        $user = User::create($validated);

        $user->assignRole('guest');

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('verification.notice', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="text-center lg:text-left mb-8">
        <h2 class="text-2xl font-bold text-surface-900 tracking-tight">Create account</h2>
        <p class="text-sm text-surface-500 mt-1.5">Join the OLLMCHS Library community</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1.5 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autocomplete="username" placeholder="you@ollmchs.ac.ke" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1.5 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            <span wire:loading.remove wire:target="register">Create account</span>
            <span wire:loading wire:target="register" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Creating account...
            </span>
        </x-primary-button>
    </form>

    <p class="text-center text-sm text-surface-500 mt-6">
        Already have an account?
        <a href="{{ route('login') }}" wire:navigate class="text-primary-600 hover:text-primary-700 font-medium transition-colors">Sign in</a>
    </p>

    <p class="text-center text-xs text-surface-400 mt-2">
        Are you a student?
        <a href="{{ route('register.student') }}" wire:navigate class="text-primary-600 hover:text-primary-700 font-medium transition-colors">Register here</a>
    </p>
</div>
