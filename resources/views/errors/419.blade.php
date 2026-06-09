<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-6xl font-bold text-surface-900 dark:text-white mb-4">419</h1>
            <h2 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">Session Expired</h2>
            <p class="text-surface-500 dark:text-surface-400 mb-8">Your session has expired. Please sign in again to continue.</p>
            <a href="{{ route('login') }}" wire:navigate class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign in again
            </a>
        </div>
    </div>
</x-guest-layout>
