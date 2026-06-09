<div>
    <x-header title="System Health" subtitle="Monitor system status, run diagnostics, and optimize performance">
        <x-slot:actions>
            <button wire:click="refreshHealth" wire:loading.attr="disabled" class="btn-primary btn-sm">
                <svg wire:loading.remove wire:target="refreshHealth" class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="refreshHealth" class="w-4 h-4 mr-1 inline animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span wire:loading.remove wire:target="refreshHealth">Run Health Check</span>
                <span wire:loading wire:target="refreshHealth">Running...</span>
            </button>
        </x-slot:actions>
    </x-header>

    {{-- Tabs --}}
    <div class="flex gap-2 mb-6">
        <button wire:click="setTab('health')"
            class="btn-{{ $activeTab === 'health' ? 'primary' : 'secondary' }} btn-sm">
            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Health Dashboard
        </button>
        <button wire:click="setTab('optimization')"
            class="btn-{{ $activeTab === 'optimization' ? 'primary' : 'secondary' }} btn-sm">
            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Optimization
        </button>
    </div>

    @if($activeTab === 'health')
        {{-- Overall Status Banner --}}
        @if(!empty($overall))
            <div class="mb-6 p-4 rounded-xl border
                @switch($overall['overall'])
                    @case('healthy') bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 @break
                    @case('warning') bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 @break
                    @case('critical') bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 @break
                @endswitch">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                        @switch($overall['overall'])
                            @case('healthy') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 @break
                            @case('warning') bg-amber-100 dark:bg-amber-900/30 text-amber-600 @break
                            @case('critical') bg-red-100 dark:bg-red-900/30 text-red-600 @break
                        @endswitch">
                        @switch($overall['overall'])
                            @case('healthy')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @break
                            @case('warning')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                @break
                            @case('critical')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                @break
                        @endswitch
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-surface-900 dark:text-white capitalize">
                            System Status: {{ $overall['overall'] }}
                        </p>
                        <p class="text-sm text-surface-500 dark:text-surface-400">
                            {{ $overall['healthy'] }} of {{ $overall['total'] }} checks passed
                            &middot; {{ $overall['warning'] }} warnings
                            &middot; {{ $overall['critical'] }} critical
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Summary Stats --}}
        @if(!empty($overall))
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Healthy</p>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $overall['healthy'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Warnings</p>
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $overall['warning'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <p class="text-xs font-medium text-red-600 dark:text-red-400">Critical</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $overall['critical'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800">
                    <p class="text-xs font-medium text-primary-600 dark:text-primary-400">Total Checks</p>
                    <p class="text-2xl font-bold text-primary-700 dark:text-primary-300">{{ $overall['total'] }}</p>
                </div>
            </div>
        @endif

        {{-- Loading state --}}
        <div wire:loading wire:target="refreshHealth" class="text-center py-12">
            <svg class="w-10 h-10 mx-auto text-primary-500 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <p class="text-sm text-surface-500">Running health checks...</p>
        </div>

        {{-- Health Checks List --}}
        <div wire:loading.remove wire:target="refreshHealth" class="space-y-4">
            @foreach($checks as $key => $check)
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center
                                    @switch($check['status'])
                                        @case('healthy') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 @break
                                        @case('warning') bg-amber-100 dark:bg-amber-900/30 text-amber-600 @break
                                        @case('critical') bg-red-100 dark:bg-red-900/30 text-red-600 @break
                                    @endswitch">
                                    @switch($check['status'])
                                        @case('healthy')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            @break
                                        @case('warning')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01" />
                                            </svg>
                                            @break
                                        @case('critical')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            @break
                                    @endswitch
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-semibold text-surface-900 dark:text-white">{{ $check['label'] }}</h3>
                                    @if(!empty($check['details']))
                                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($check['details'] as $detailKey => $detailValue)
                                                <div class="text-xs">
                                                    <span class="text-surface-400">{{ $detailKey }}:</span>
                                                    <span class="font-medium text-surface-700 dark:text-surface-300 ml-1">{{ $detailValue }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($check['issues']))
                                        <div class="mt-2 space-y-1">
                                            @foreach($check['issues'] as $issue)
                                                <p class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" />
                                                    </svg>
                                                    {{ $issue }}
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($check['recommendations']))
                                        <div class="mt-2 space-y-1">
                                            @foreach($check['recommendations'] as $rec)
                                                <p class="text-xs text-primary-600 dark:text-primary-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $rec }}
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <span class="shrink-0 px-2 py-0.5 text-xs font-medium rounded-full
                                    @switch($check['status'])
                                        @case('healthy') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 @break
                                        @case('warning') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @break
                                        @case('critical') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @break
                                    @endswitch">
                                    {{ ucfirst($check['status']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Recommendations --}}
            @if(!empty($overall['recommendations']))
                <div class="card border-primary-200 dark:border-primary-800">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Recommendations</h3>
                    </div>
                    <div class="card-body space-y-3">
                        @foreach($overall['recommendations'] as $rec)
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-50 dark:bg-surface-800">
                                <div class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center
                                    @if($rec['severity'] === 'critical') bg-red-100 text-red-600
                                    @elseif($rec['severity'] === 'warning') bg-amber-100 text-amber-600
                                    @else bg-primary-100 text-primary-600 @endif">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-surface-700 dark:text-surface-300">{{ $rec['message'] }}</p>
                                    <p class="text-xs text-surface-400 mt-0.5">Check: {{ ucfirst($rec['check']) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    @elseif($activeTab === 'optimization')
        {{-- Optimization Center --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Clear Cache --}}
            <div class="card">
                <div class="card-body text-center py-8">
                    <div class="w-14 h-14 mx-auto rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Clear Cache</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">
                        Clears route, view, config, and application cache
                    </p>
                    @can('manage-system-optimization')
                        <button wire:click="clearCache" wire:confirm="Clear all application caches? This may temporarily slow down the application."
                            class="btn-outline w-full justify-center" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="clearCache">Clear Cache</span>
                            <span wire:loading wire:target="clearCache">
                                <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                Clearing...
                            </span>
                        </button>
                    @else
                        <p class="text-xs text-surface-400">You don't have permission</p>
                    @endcan
                </div>
            </div>

            {{-- Rebuild Cache --}}
            <div class="card">
                <div class="card-body text-center py-8">
                    <div class="w-14 h-14 mx-auto rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Rebuild Cache</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">
                        Rebuilds config cache, route cache, and view cache
                    </p>
                    @can('manage-system-optimization')
                        <button wire:click="rebuildCache" wire:confirm="Rebuild all caches? This will compile configuration and routes."
                            class="btn-outline w-full justify-center" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="rebuildCache">Rebuild Cache</span>
                            <span wire:loading wire:target="rebuildCache">
                                <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                Rebuilding...
                            </span>
                        </button>
                    @else
                        <p class="text-xs text-surface-400">You don't have permission</p>
                    @endcan
                </div>
            </div>

            {{-- Optimize System --}}
            <div class="card">
                <div class="card-body text-center py-8">
                    <div class="w-14 h-14 mx-auto rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Optimize System</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">
                        Runs framework optimization and application health checks
                    </p>
                    @can('manage-system-optimization')
                        <button wire:click="optimizeSystem" wire:confirm="Run full system optimization? This may temporarily slow down the application."
                            class="btn-primary w-full justify-center" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="optimizeSystem">Optimize System</span>
                            <span wire:loading wire:target="optimizeSystem">
                                <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                Optimizing...
                            </span>
                        </button>
                    @else
                        <p class="text-xs text-surface-400">You don't have permission</p>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Execution Logs --}}
        @if(!empty($optimizationLogs))
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Execution Logs</h3>
                </div>
                <div class="card-body space-y-2">
                    @foreach($optimizationLogs as $log)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-50 dark:bg-surface-800">
                            <div class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center
                                @if($log['status'] === 'success') bg-emerald-100 text-emerald-600
                                @else bg-red-100 text-red-600 @endif">
                                @if($log['status'] === 'success')
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-surface-900 dark:text-white font-mono">{{ $log['command'] }}</p>
                                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full
                                        @if($log['status'] === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                        @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @endif">
                                        {{ ucfirst($log['status']) }}
                                    </span>
                                </div>
                                @if(!empty($log['output']))
                                    <pre class="mt-1 text-xs text-surface-500 dark:text-surface-400 whitespace-pre-wrap font-mono">{{ Str::limit(trim($log['output']), 200) }}</pre>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Info card --}}
        <div class="card mt-4">
            <div class="card-body text-sm text-surface-500 dark:text-surface-400">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 text-primary-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>
                        All optimization actions are logged to the activity log for audit trail purposes.
                        These actions may temporarily affect application performance while caches are being rebuilt.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
