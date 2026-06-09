<?php

use Livewire\Volt\Component;

new class extends Component
{
    public bool $enabled = false;
    public ?string $qrCodeUrl = null;
    public ?string $secret = null;
    public string $code = '';
    public bool $packageAvailable = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->enabled = (bool) $user->two_factor_enabled;
        $this->packageAvailable = class_exists('PragmaRX\Google2FA\Google2FA');
    }

    public function enable(): void
    {
        if (!$this->packageAvailable) {
            $this->dispatch('notify', message: 'Two-factor authentication requires the google2fa package. Run: composer require pragmarx/google2fa', type: 'error');
            return;
        }
        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => false,
        ]);
        $this->secret = $secret;
        $this->qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    public function confirm(): void
    {
        $this->validate(['code' => 'required|string|size:6']);
        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $this->code
        );
        if (!$valid) {
            $this->addError('code', 'The verification code is invalid.');
            return;
        }
        $user->update(['two_factor_enabled' => true]);
        $this->enabled = true;
        $this->secret = null;
        $this->qrCodeUrl = null;
        $this->code = '';
        $this->dispatch('notify', message: 'Two-factor authentication enabled.', type: 'success');
    }

    public function disable(): void
    {
        auth()->user()->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);
        $this->enabled = false;
        $this->dispatch('notify', message: 'Two-factor authentication disabled.', type: 'success');
    }
}; ?>

<section class="card">
    <div class="card-header">
        <h3 class="font-semibold text-surface-900 dark:text-white">Two-Factor Authentication</h3>
    </div>
    <div class="card-body">
        <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">Add an extra layer of security to your account using a time-based one-time password from your authenticator app.</p>

        @if(!$packageAvailable)
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Two-Factor Authentication Not Available</p>
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Install the google2fa package to enable this feature: <code class="bg-amber-100 dark:bg-amber-800 px-1.5 py-0.5 rounded">composer require pragmarx/google2fa</code></p>
            </div>
        @elseif($enabled)
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 mb-4">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Two-factor authentication is enabled</p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">Your account is protected with 2FA</p>
                    </div>
                </div>
            </div>
            <button wire:click="disable" wire:confirm="Disable two-factor authentication?" class="btn-outline text-sm text-accent-600">
                Disable Two-Factor Authentication
            </button>
        @elseif($qrCodeUrl)
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20">
                    <p class="text-sm font-medium text-surface-900 dark:text-white mb-3">Scan this QR code with your authenticator app</p>
                    <div class="flex justify-center mb-4">
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="w-48 h-48">
                    </div>
                    <p class="text-xs text-surface-500 text-center">Or manually enter the secret key: <code class="bg-surface-200 dark:bg-surface-700 px-2 py-0.5 rounded text-xs">{{ $secret }}</code></p>
                </div>
                <div>
                    <label class="label">Enter the 6-digit code from your authenticator app</label>
                    <div class="flex items-end gap-3">
                        <div class="max-w-xs">
                            <input type="text" wire:model="code" class="input-field w-full text-center text-lg tracking-widest" maxlength="6" placeholder="000000">
                            @error('code') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button wire:click="confirm" class="btn-primary">Verify & Enable</button>
                    </div>
                </div>
            </div>
        @else
            <button wire:click="enable" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Enable Two-Factor Authentication
            </button>
        @endif
    </div>
</section>
