<div class="stat-carousel mb-6">
    @foreach ($stats as $stat)
        <div class="stat-card">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400">{{ $stat['label'] }}</p>
            <p class="text-3xl font-bold text-surface-900 dark:text-white mt-1">{{ $stat['value'] }}</p>
        </div>
    @endforeach
</div>
