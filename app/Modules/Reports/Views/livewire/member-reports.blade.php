<div>
    <div class="page-header">
        <h1 class="page-title">Member Reports</h1>
        <p class="page-subtitle">Member statistics and activity</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="stat-card"><p class="stat-label">Total Members</p><p class="stat-value">{{ $stats['total_members'] }}</p></div>
        <div class="stat-card bg-emerald-50"><p class="stat-label text-emerald-600">Active</p><p class="stat-value text-emerald-600">{{ $stats['active_members'] }}</p></div>
        <div class="stat-card bg-red-50"><p class="stat-label text-red-600">Suspended</p><p class="stat-value text-red-600">{{ $stats['suspended_members'] }}</p></div>
        <div class="stat-card bg-blue-50"><p class="stat-label text-blue-600">New This Month</p><p class="stat-value text-blue-600">{{ $stats['new_this_month'] }}</p></div>
        <div class="stat-card"><p class="stat-label">With Library Cards</p><p class="stat-value">{{ $stats['with_library_cards'] }}</p></div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="font-semibold mb-4">Status Distribution</h3>
            <div class="space-y-3">
                @php
                    $total = max($stats['total_members'], 1);
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Active</span>
                        <span>{{ $stats['active_members'] }} ({{ round($stats['active_members'] / $total * 100) }}%)</span>
                    </div>
                    <div class="bg-surface-100 dark:bg-surface-700 rounded-full h-3 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $stats['active_members'] / $total * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Suspended</span>
                        <span>{{ $stats['suspended_members'] }} ({{ round($stats['suspended_members'] / $total * 100) }}%)</span>
                    </div>
                    <div class="bg-surface-100 dark:bg-surface-700 rounded-full h-3 overflow-hidden">
                        <div class="bg-red-500 h-full rounded-full" style="width: {{ $stats['suspended_members'] / $total * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
