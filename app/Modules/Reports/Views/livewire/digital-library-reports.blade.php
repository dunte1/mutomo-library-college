<div>
    <div class="page-header">
        <h1 class="page-title">Digital Library Reports</h1>
        <p class="page-subtitle">Digital asset usage and engagement</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card"><p class="stat-label">Total Assets</p><p class="stat-value">{{ $stats['total_assets'] }}</p></div>
        <div class="stat-card bg-emerald-50"><p class="stat-label text-emerald-600">Downloads</p><p class="stat-value text-emerald-600">{{ number_format($stats['total_downloads']) }}</p></div>
        <div class="stat-card bg-blue-50"><p class="stat-label text-blue-600">Total Views</p><p class="stat-value text-blue-600">{{ number_format($stats['total_views']) }}</p></div>
        <div class="stat-card"><p class="stat-label">Categories</p><p class="stat-value">{{ $stats['total_categories'] }}</p></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-body">
                <h3 class="font-semibold mb-2">Most Viewed</h3>
                <p class="text-lg font-bold text-primary-600">{{ $stats['most_viewed'] }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="font-semibold mb-2">Most Downloaded</h3>
                <p class="text-lg font-bold text-primary-600">{{ $stats['most_downloaded'] }}</p>
            </div>
        </div>
    </div>
</div>
