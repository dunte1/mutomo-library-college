<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="card">
    <div class="card-header">
        <h3 class="font-semibold text-surface-900 dark:text-white">Change Password</h3>
    </div>
    <div class="card-body">
        <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">Ensure your account is using a strong password to stay secure.</p>

        <form wire:submit="updatePassword" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label" for="pw_current">Current Password</label>
                    <input wire:model="current_password" id="pw_current" type="password" class="input-field w-full" autocomplete="current-password">
                    @error('current_password') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label" for="pw_new">New Password</label>
                    <input wire:model="password" id="pw_new" type="password" class="input-field w-full" autocomplete="new-password">
                    @error('password') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="pw_confirm">Confirm New Password</label>
                    <input wire:model="password_confirmation" id="pw_confirm" type="password" class="input-field w-full" autocomplete="new-password">
                    @error('password_confirmation') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center gap-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Update Password</span>
                    <span wire:loading>Updating...</span>
                </button>
                <div wire:loading.remove wire:target="password">
                    <x-action-message class="me-3" on="password-updated">Saved.</x-action-message>
                </div>
            </div>
        </form>
    </div>
</section>
