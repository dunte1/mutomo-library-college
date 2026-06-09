<div class="page-header flex items-center justify-between mb-6">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="shrink-0">{{ $actions }}</div>
    @endisset
</div>
