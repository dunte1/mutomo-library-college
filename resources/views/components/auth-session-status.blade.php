@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium']) }}>
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $status }}</span>
        </div>
    </div>
@endif
