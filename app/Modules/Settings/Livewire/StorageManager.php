<?php

namespace App\Modules\Settings\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class StorageManager extends Component
{
    public array $disks = [];

    public array $logs = [];

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $disks = ['local', 'public'];

        foreach ($disks as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                $files = $disk->allFiles();
                $totalSize = 0;
                $fileCount = count($files);

                foreach ($files as $file) {
                    try {
                        $totalSize += $disk->size($file);
                    } catch (\Throwable) {
                        // skip inaccessible files
                    }
                }

                $this->disks[$diskName] = [
                    'name' => $diskName,
                    'driver' => config("filesystems.disks.{$diskName}.driver", 'unknown'),
                    'file_count' => $fileCount,
                    'total_size' => $totalSize,
                    'total_size_formatted' => $this->formatBytes($totalSize),
                    'root' => $disk->path(''),
                ];
            } catch (\Throwable $e) {
                $this->disks[$diskName] = [
                    'name' => $diskName,
                    'error' => $e->getMessage(),
                ];
            }
        }
    }

    public function clearTemp(): void
    {
        try {
            $this->authorize('manage-storage');
            $disk = Storage::disk('local');
            $count = 0;
            foreach ($disk->files('temp') as $file) {
                if ($disk->delete($file)) {
                    $count++;
                }
            }
            foreach ($disk->files('framework/cache/data') as $file) {
                if (! str_contains($file, '.gitignore') && $disk->delete($file)) {
                    $count++;
                }
            }
            $this->refreshStats();
            $this->dispatch('notify', type: 'success', message: "Cleaned {$count} temporary files.");
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to clean temporary files: '.$e->getMessage());
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function render()
    {
        return view('settings::livewire.storage-manager', [
            'disks' => $this->disks,
        ]);
    }
}
