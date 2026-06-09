@php
    $variant = $attributes->get('variant', 'default');
    $classes = match ($variant) {
        'success' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
        'danger' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
        'warning' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
        'info' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
        default => 'bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>
