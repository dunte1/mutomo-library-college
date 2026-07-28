<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Clear cached permissions so role changes take effect immediately
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $user = auth()->user();

        // Permission-based redirect: staff go to admin dashboard, patrons go to catalog
        if ($user->hasAnyPermission(['view-dashboard', 'manage-settings', 'view-circulation'])) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } else {
            // Students & lecturers go to browse the catalog (their primary use-case)
            $this->redirectIntended(default: route('catalog.books.index', absolute: false), navigate: true);
        }
    }
}; ?>

<div>
    <div class="text-center lg:text-left mb-8">
        <h2 class="text-2xl font-bold text-surface-900 tracking-tight">Welcome back</h2>
        <p class="text-sm text-surface-500 mt-1.5">Sign in to access your library account</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="you@ollmchs.ac.ke" />
            <x-input-error :messages="$errors->get('form.email')" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-primary-600 hover:text-primary-700 font-medium transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot?') }}
                    </a>
                @endif
            </div>
            <x-text-input wire:model="form.password" id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="current-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
            <x-input-error :messages="$errors->get('form.password')" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center gap-2 cursor-pointer group">
                <div class="relative">
                    <input wire:model="form.remember" id="remember" type="checkbox"
                        class="peer sr-only"
                        name="remember">
                    <div class="w-4 h-4 rounded border-2 border-surface-300 bg-white transition-all peer-checked:bg-primary-600 peer-checked:border-primary-600 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500 peer-focus-visible:ring-offset-2"></div>
                    <svg class="absolute top-0 left-0 w-4 h-4 text-white pointer-events-none hidden peer-checked:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="text-sm text-surface-600 group-hover:text-surface-800 transition-colors select-none">{{ __('Remember me') }}</span>
            </label>
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Signing in...
            </span>
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="text-center text-sm text-surface-500 mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" wire:navigate class="text-primary-600 hover:text-primary-700 font-medium transition-colors">Create one</a>
        </p>
    @endif
</div>
