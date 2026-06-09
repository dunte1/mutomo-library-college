@section('title', 'Two-Factor Verification')
<div class="min-h-screen flex items-center justify-center bg-surface-50 dark:bg-surface-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                <svg class="h-8 w-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="mt-4 text-3xl font-bold text-surface-900 dark:text-white">Two-Factor Authentication</h2>
            <p class="mt-2 text-sm text-surface-500 dark:text-surface-400">Enter the verification code from your authenticator app.</p>
        </div>

        <form wire:submit="verify" class="card">
            <div class="card-body space-y-6">
                @if(session('error'))
                    <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <div>
                    <label class="label">Verification Code</label>
                    <input type="text" wire:model="code" class="input-field text-center text-2xl tracking-widest" maxlength="6" placeholder="000000" autocomplete="one-time-code" inputmode="numeric">
                    @error("code") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary w-full flex justify-center">
                    Verify
                </button>
            </div>
        </form>
    </div>
</div>
