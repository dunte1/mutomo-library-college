@php
    $variant = $attributes->get('variant', 'default');
    $primary = $attributes->has('primary');
    $sm = $attributes->has('sm');
    $href = $attributes->get('href');

    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    if ($primary) {
        $variantClasses = 'bg-primary-600 text-white hover:bg-primary-700 focus-visible:ring-primary-500 shadow-sm';
    } else {
        $variantClasses = match ($variant) {
            'danger' => 'text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/20',
            'warning' => 'text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20',
            'success' => 'text-emerald-600 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20',
            default => 'text-surface-700 dark:text-surface-300 border border-surface-300 dark:border-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700',
        };
    }

    $sizeClasses = $sm ? 'px-3 py-1.5 text-xs gap-1.5' : 'px-4 py-2.5 text-sm gap-2';
    $class = trim("$baseClasses $variantClasses $sizeClasses " . $attributes->get('class', ''));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->except(['href', 'variant', 'primary', 'sm'])->merge(['class' => $class]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->except(['variant', 'primary', 'sm', 'href'])->merge(['type' => 'button', 'class' => $class]) }}>
        {{ $slot }}
    </button>
@endif
