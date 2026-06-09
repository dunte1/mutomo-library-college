@section('title', 'Communication Analytics')
<div>
    <x-slot name="header">Communication Analytics</x-slot>
    <x-slot name="subtitle">Track message delivery and engagement</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Overview</h2>
        <select wire:model.live="period" class="input-field w-40">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="365">Last year</option>
        </select>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4">
            <p class="text-sm text-surface-500">Total Messages</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $stats['total_sent'] ?? 0 }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-surface-500">Sent This Period</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['messages_this_month'] ?? 0 }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-surface-500">Read Rate</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                @if(($trends['read_rate']['total'] ?? 0) > 0)
                    {{ round(($trends['read_rate']['read'] / $trends['read_rate']['total']) * 100) }}%
                @else
                    0%
                @endif
            </p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-surface-500">Scheduled</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['total_scheduled'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="font-semibold text-surface-900 dark:text-white mb-4">By Type</h3>
            @if(!empty($trends['by_type']))
                <div class="space-y-3">
                    @foreach($trends['by_type'] as $type => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-surface-600 dark:text-surface-400 capitalize">{{ $type }}</span>
                        <span class="font-medium text-surface-900 dark:text-white">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-surface-100 dark:bg-surface-700 rounded-full h-2">
                        @php $max = max($trends['by_type']); @endphp
                        <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $max > 0 ? ($count / $max) * 100 : 0 }}%"></div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-surface-400">No data available for this period.</p>
            @endif
        </div>

        <div class="card p-6">
            <h3 class="font-semibold text-surface-900 dark:text-white mb-4">By Priority</h3>
            @if(!empty($trends['by_priority']))
                <div class="space-y-3">
                    @foreach($trends['by_priority'] as $priority => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-surface-600 dark:text-surface-400 capitalize">{{ $priority }}</span>
                        <span class="font-medium text-surface-900 dark:text-white">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-surface-100 dark:bg-surface-700 rounded-full h-2">
                        @php $max = max($trends['by_priority']); @endphp
                        <div class="bg-{{ $priority === 'high' ? 'accent' : ($priority === 'normal' ? 'primary' : 'surface') }}-500 h-2 rounded-full"
                            style="width: {{ $max > 0 ? ($count / $max) * 100 : 0 }}%"></div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-surface-400">No data available for this period.</p>
            @endif
        </div>
    </div>

    <div class="card p-6">
        <h3 class="font-semibold text-surface-900 dark:text-white mb-4">Events</h3>
        @if(!empty($trends['events']))
            <div class="space-y-3">
                @foreach($trends['events'] as $event => $count)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-surface-600 dark:text-surface-400 capitalize">{{ $event }}</span>
                    <span class="font-medium text-surface-900 dark:text-white">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-surface-400">No events recorded for this period.</p>
        @endif
    </div>
</div>
