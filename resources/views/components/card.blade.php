@php
    $title = $attributes->get('title');
@endphp
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-surface-800 rounded-2xl shadow-soft border border-surface-100 dark:border-surface-700']) }}>
    @if ($title)
        <div class="px-6 py-4 border-b border-surface-100 dark:border-surface-700">
            <h3 class="text-lg font-semibold text-surface-900 dark:text-white">{{ $title }}</h3>
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
