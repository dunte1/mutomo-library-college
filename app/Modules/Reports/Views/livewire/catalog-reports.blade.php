<div>
    <div class="page-header">
        <h1 class="page-title">Catalog Reports</h1>
        <p class="page-subtitle">Books, copies, categories, and author statistics</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="stat-card"><p class="stat-label">Total Books</p><p class="stat-value">{{ $stats['total_books'] }}</p></div>
        <div class="stat-card"><p class="stat-label">Total Copies</p><p class="stat-value">{{ $stats['total_copies'] }}</p></div>
        <div class="stat-card bg-emerald-50"><p class="stat-label text-emerald-600">Available</p><p class="stat-value text-emerald-600">{{ $stats['available_copies'] }}</p></div>
        <div class="stat-card"><p class="stat-label">Categories</p><p class="stat-value">{{ $stats['total_categories'] }}</p></div>
        <div class="stat-card"><p class="stat-label">Authors</p><p class="stat-value">{{ $stats['total_authors'] }}</p></div>
        <div class="stat-card"><p class="stat-label">Publishers</p><p class="stat-value">{{ $stats['total_publishers'] }}</p></div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold mb-4">Books by Category</h3>
            <div class="space-y-3">
                @foreach($categoryDistribution as $cat)
                <div class="flex items-center gap-4">
                    <span class="text-sm w-48 truncate">{{ $cat['name'] }}</span>
                    <div class="flex-1 bg-surface-100 dark:bg-surface-700 rounded-full h-3 overflow-hidden">
                        <div class="bg-primary-500 h-full rounded-full" style="width: {{ $stats['total_books'] > 0 ? ($cat['books_count'] / $stats['total_books'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-sm font-medium w-16 text-right">{{ $cat['books_count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
