<div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
    <div class="flex-1 flex flex-wrap items-center gap-2 w-full sm:w-auto">
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="shrink-0">{{ $actions }}</div>
    @endisset
</div>
