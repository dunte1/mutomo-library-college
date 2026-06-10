<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Downloads</h1>
            <p class="page-subtitle">Digital asset download statistics</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stat-card">
            <p class="stat-label">Total Downloads</p>
            <p class="stat-value">{{ number_format($stats['total_downloads']) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Assets</p>
            <p class="stat-value">{{ $stats['total_assets'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Most Downloaded</p>
            <p class="stat-value text-sm truncate">{{ $stats['most_downloaded']?->title ?? 'N/A' }}</p>
            @if($stats['most_downloaded'])
            <p class="text-xs text-surface-400">{{ $stats['most_downloaded']->times_downloaded }} downloads</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce="search" placeholder="Search assets..." class="input w-full">
                </div>
            </div>

            <div class="overflow-x-auto table-mobile-cards">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>
                                <button wire:click="$set('sort', 'times_downloaded')" class="flex items-center gap-1 hover:text-primary-600">
                                    Downloads
                                    @if($sort === 'times_downloaded')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $direction === 'desc' ? 'M19 14l-7 7m0 0l-7-7m7 7V3' : 'M5 10l7-7m0 0l7 7m-7-7v18' }}" />
                                    </svg>
                                    @endif
                                </button>
                            </th>
                            <th>Type</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        <tr>
                            <td class="font-medium">
                                <a href="{{ route('digital-library.show', $asset->id) }}" wire:navigate class="hover:text-primary-600">
                                    {{ $asset->title }}
                                </a>
                            </td>
                            <td class="text-sm text-surface-500">{{ $asset->category?->name ?? '—' }}</td>
                            <td class="text-sm font-semibold">{{ number_format($asset->times_downloaded) }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($asset->file_type ?? '—') }}</span></td>
                            <td class="text-sm text-surface-500">{{ $asset->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-surface-400">No download data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </div>
    </div>
</div>
