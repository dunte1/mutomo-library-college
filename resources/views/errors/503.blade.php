<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-6xl font-bold text-surface-900 dark:text-white mb-4">503</h1>
            <h2 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">Under Maintenance</h2>
            <p class="text-surface-500 dark:text-surface-400 mb-8">We're performing scheduled maintenance. Please check back shortly.</p>
            <button onclick="location.reload()" class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>
</x-guest-layout>