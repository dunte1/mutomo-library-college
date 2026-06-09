<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Storage Manager</h1>
            <p class="page-subtitle">Monitor and manage storage usage</p>
        </div>
        @can('manage-storage')
        <button wire:click="clearTemp" wire:confirm="Clear temporary files? This will remove temp cache files." class="btn-sm btn-warning">Clear Temp Files</button>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($disks as $name => $disk)
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-semibold capitalize mb-4">{{ $name }} Disk</h3>
                @if(isset($disk['error']))
                <p class="text-red-500 text-sm">{{ $disk['error'] }}</p>
                @else
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-surface-500">Driver</dt>
                        <dd class="font-medium">{{ $disk['driver'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-surface-500">Files</dt>
                        <dd class="font-medium">{{ number_format($disk['file_count']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-surface-500">Total Size</dt>
                        <dd class="font-medium">{{ $disk['total_size_formatted'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-surface-500">Root Path</dt>
                        <dd class="font-mono text-xs truncate max-w-[200px]">{{ $disk['root'] }}</dd>
                    </div>
                </dl>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
