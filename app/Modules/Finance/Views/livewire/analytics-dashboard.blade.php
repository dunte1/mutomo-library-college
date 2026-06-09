@section('title', 'Analytics')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Analytics</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Overdue Rate</p>
            <p class="stat-value @if(($analytics['overdue_rate'] ?? 0) > 20) text-red-600 @else text-green-600 @endif">
                {{ $analytics['overdue_rate'] ?? 0 }}%
            </p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Borrows (6mo)</p>
            <p class="stat-value">{{ array_sum($analytics['borrow_trends'] ?? []) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Collected (6mo)</p>
            <p class="stat-value">KES {{ number_format(array_sum($analytics['collection_trends'] ?? []), 0) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Peak Hour</p>
            <p class="stat-value">
                @php
                    $peakHour = !empty($analytics['peak_hours']) ? array_search(max($analytics['peak_hours']), $analytics['peak_hours']) : null;
                @endphp
                {{ $peakHour ? $peakHour . ':00' : 'N/A' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Borrow Trends</h3>
            @if(!empty($analytics['borrow_trends']))
                <div class="flex items-end gap-3 h-40">
                    @php $max = max($analytics['borrow_trends']); @endphp
                    @foreach($analytics['borrow_trends'] as $month => $count)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs text-gray-500">{{ $count }}</span>
                            <div class="w-full bg-primary-200 dark:bg-primary-900/30 rounded-t transition-all"
                                 style="height: {{ $count > 0 ? max(($count / $max) * 100, 4) : 1 }}%">
                            </div>
                            <span class="text-xs text-gray-500 mt-1">{{ $month }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">No data available yet</p>
            @endif
        </div>

        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Peak Borrowing Hours</h3>
            @if(!empty($analytics['peak_hours']))
                <div class="flex items-end gap-1 h-40">
                    @foreach(range(6, 22) as $hour)
                        @php $count = $analytics['peak_hours'][sprintf('%02d', $hour)] ?? 0; @endphp
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs text-gray-400">{{ $count }}</span>
                            <div class="w-full bg-amber-200 dark:bg-amber-900/30 rounded-t transition-all"
                                 style="height: {{ $count > 0 ? max(($count / (max($analytics['peak_hours']) ?: 1)) * 100, 2) : 1 }}%">
                            </div>
                            <span class="text-xs text-gray-500">{{ sprintf('%02d', $hour) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">No data available yet</p>
            @endif
        </div>

        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Popular Categories</h3>
            @if(!empty($analytics['popular_categories']))
                <ul class="space-y-3">
                    @foreach($analytics['popular_categories'] as $cat)
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $cat['name'] }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    @php $maxCat = max(array_column($analytics['popular_categories'], 'count')); @endphp
                                    <div class="bg-primary-600 rounded-full h-2"
                                         style="width: {{ $maxCat > 0 ? ($cat['count'] / $maxCat) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8 text-right">{{ $cat['count'] }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-400">No data available yet</p>
            @endif
        </div>

        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Department Usage</h3>
            @if(!empty($analytics['department_usage']))
                <ul class="space-y-2">
                    @foreach($analytics['department_usage'] as $dept)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $dept['department'] }}</span>
                            <span class="badge badge-info">{{ $dept['count'] }} borrows</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-400">No data available yet</p>
            @endif
        </div>
    </div>
</div>
