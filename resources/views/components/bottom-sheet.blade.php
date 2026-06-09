@props([
    'name',
    'show'    => false,
    'title'   => null,
    'maxWidth' => 'lg',   /* used on desktop as modal max-width */
])

@php
$maxWidthClass = [
    'sm'  => 'sm:max-w-sm',
    'md'  => 'sm:max-w-md',
    'lg'  => 'sm:max-w-lg',
    'xl'  => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{ show: @js($show) }"
    x-init="$watch('show', val => {
        if (val) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    })"
    x-on:open-bottom-sheet.window="$event.detail == '{{ $name }}' || ($event.detail && $event.detail[0] == '{{ $name }}') ? show = true : null"
    x-on:close-bottom-sheet.window="$event.detail == '{{ $name }}' || ($event.detail && $event.detail[0] == '{{ $name }}') ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    style="display: {{ $show ? 'flex' : 'none' }}; z-index: 60;"
    class="fixed inset-0 md:flex md:items-center md:justify-center md:px-4"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-on:click="show = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="bottom-sheet-backdrop"
    ></div>

    {{-- Panel --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 md:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 md:scale-100"
        x-transition:leave-end="opacity-0 translate-y-full md:translate-y-0 md:scale-95"
        class="bottom-sheet-panel w-full {{ $maxWidthClass }}"
    >
        {{-- Drag handle (mobile only) --}}
        <div class="bottom-sheet-handle"></div>

        {{-- Optional title bar --}}
        @if ($title)
            <div class="flex items-center justify-between px-5 py-4 border-b border-surface-100 dark:border-surface-700">
                <h3 class="text-base font-semibold text-surface-900 dark:text-white">{{ $title }}</h3>
                <button
                    x-on:click="show = false"
                    class="p-1.5 rounded-full text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors"
                    aria-label="Close"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Content --}}
        <div class="overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>
