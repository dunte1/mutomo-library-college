<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-accent-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1110 3.636M15 3a3 3 0 013 3v2" />
                </svg>
            </div>
            <h1 class="text-6xl font-bold text-surface-900 dark:text-white mb-4">403</h1>
            <h2 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">Access Denied</h2>
            <p class="text-surface-500 dark:text-surface-400 mb-8">You don't have permission to access this resource.</p>
            <a href="{{ route('dashboard') }}" wire:navigate class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</x-guest-layout>