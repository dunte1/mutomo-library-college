<div>
    <div class="page-header">
        <h1 class="page-title">Cache Management</h1>
        <p class="page-subtitle">Manage application cache and optimization</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-semibold mb-4">Cache Status</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-surface-500">Default Store</dt>
                        <dd class="font-medium">{{ $cacheStats['default_store'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-surface-500">Config Optimized</dt>
                        <dd><span class="badge {{ $cacheStats['optimized'] ? 'badge-success' : 'badge-secondary' }}">{{ $cacheStats['optimized'] ? 'Yes' : 'No' }}</span></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-surface-500">Routes Cached</dt>
                        <dd><span class="badge {{ $cacheStats['routes_cached'] ? 'badge-success' : 'badge-secondary' }}">{{ $cacheStats['routes_cached'] ? 'Yes' : 'No' }}</span></dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-semibold mb-4">Actions</h3>
                <div class="space-y-3">
                    <button wire:click="clearAll" wire:confirm="Clear all cache? This may slow down the first request." class="btn-primary w-full justify-center">
                        Clear All Cache
                    </button>
                    <button wire:click="optimizeSystem" class="btn-secondary w-full justify-center">
                        Optimize System
                    </button>
                    <button wire:click="clearConfig" class="btn-secondary w-full justify-center">
                        Clear Config Cache
                    </button>
                    <button wire:click="clearRoutes" class="btn-secondary w-full justify-center">
                        Clear Route Cache
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($logs)
    <div class="card mt-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold mb-2">Output</h3>
            <pre class="bg-surface-100 dark:bg-surface-800 p-4 rounded-lg text-sm overflow-x-auto">{{ implode("\n", $logs) }}</pre>
        </div>
    </div>
    @endif
</div>
