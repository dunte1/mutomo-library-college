<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Circulation Reports</h1>
            <p class="page-subtitle">Borrowing activity, returns, and fines</p>
        </div>
        <select wire:model.live="period" class="input w-48">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="365">Last year</option>
            <option value="all">All time</option>
        </select>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="stat-card"><p class="stat-label">Total Borrows</p><p class="stat-value">{{ $stats['total_borrows'] }}</p></div>
        <div class="stat-card bg-blue-50"><p class="stat-label text-blue-600">Active</p><p class="stat-value text-blue-600">{{ $stats['active_borrows'] }}</p></div>
        <div class="stat-card bg-red-50"><p class="stat-label text-red-600">Overdue</p><p class="stat-value text-red-600">{{ $stats['overdue_borrows'] }}</p></div>
        <div class="stat-card bg-emerald-50"><p class="stat-label text-emerald-600">Returned Today</p><p class="stat-value text-emerald-600">{{ $stats['returned_today'] }}</p></div>
        <div class="stat-card"><p class="stat-label">Reservations</p><p class="stat-value">{{ $stats['total_reservations'] }}</p></div>
        <div class="stat-card"><p class="stat-label">Total Fines</p><p class="stat-value">KES {{ number_format($stats['total_fines']) }}</p></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-body">
                <h3 class="font-semibold mb-2">Fine Summary</h3>
                <p class="text-sm text-surface-500">Pending fines amount: <span class="font-semibold text-red-600">KES {{ number_format($stats['pending_fines']) }}</span></p>
                <div class="mt-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Collected</span>
                        <span>{{ number_format($stats['total_fines'] - $stats['pending_fines']) }}</span>
                    </div>
                    <div class="bg-surface-100 dark:bg-surface-700 rounded-full h-3 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $stats['total_fines'] > 0 ? (($stats['total_fines'] - $stats['pending_fines']) / $stats['total_fines'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="font-semibold mb-2">Borrow Status</h3>
                <div class="space-y-3 mt-2">
                    <div class="flex justify-between text-sm">
                        <span>On Time</span>
                        <span class="font-medium">{{ max(0, $stats['active_borrows'] - $stats['overdue_borrows']) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-red-600">Overdue</span>
                        <span class="font-medium text-red-600">{{ $stats['overdue_borrows'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
