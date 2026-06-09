@php
    $src = $attributes->get('src');
    $alt = $attributes->get('alt', '');
    $size = $attributes->get('size', 'md');
    $dimensions = match ($size) {
        'sm' => 'w-8 h-8',
        'lg' => 'w-12 h-12',
        default => 'w-10 h-10',
    };
    $textSize = match ($size) {
        'sm' => 'text-xs',
        'lg' => 'text-lg',
        default => 'text-sm',
    };
@endphp
<div {{ $attributes->merge(['class' => "rounded-full overflow-hidden shrink-0 $dimensions"]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" class="w-full h-full object-cover">
    @else
        <div class="w-full h-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold {{ $textSize }}">
            {{ strtoupper(substr($alt, 0, 2)) }}
        </div>
    @endif
</div>
