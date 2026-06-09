<div class="flex flex-col items-center justify-center py-12 text-center">
    <div class="w-16 h-16 rounded-2xl bg-surface-100 dark:bg-surface-700 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-1">{{ $title ?? 'No data' }}</h3>
    @if($description ?? null)
        <p class="text-sm text-surface-500 dark:text-surface-400 max-w-sm">{{ $description }}</p>
    @endif
</div>
