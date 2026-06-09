@php
    $label = $attributes->get('label');
    $required = $attributes->has('required');
@endphp
<div>
    @if ($label)
        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    {{ $slot }}
</div>
