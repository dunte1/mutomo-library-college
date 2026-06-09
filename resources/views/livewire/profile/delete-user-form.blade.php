<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function deleteUser(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();
        auth()->logout();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="card border-accent-200 dark:border-accent-800">
    <div class="card-header">
        <h3 class="font-semibold text-accent-600 dark:text-accent-400">Delete Account</h3>
    </div>
    <div class="card-body">
        <p class="text-sm text-surface-600 dark:text-surface-400 mb-2">
            Once your account is deleted, all of its resources and data will be permanently deleted.
        </p>
        <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">
            Before deleting your account, please ensure you have returned all borrowed items and settled any outstanding fines.
        </p>

        <form wire:submit="deleteUser" class="space-y-4">
            <div class="max-w-md">
                <label class="label" for="del_password">Enter your password to confirm deletion</label>
                <input wire:model="password" id="del_password" type="password" class="input-field w-full" autocomplete="current-password" placeholder="Your password">
                @error('password') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <button type="submit" class="btn-danger" wire:loading.attr="disabled" wire:confirm="Are you sure you want to delete your account? This action cannot be undone.">
                    <span wire:loading.remove>Delete Account</span>
                    <span wire:loading>Deleting...</span>
                </button>
            </div>
        </form>
    </div>
</section>
