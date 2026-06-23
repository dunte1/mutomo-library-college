<?php

namespace App\Console\Commands;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanOldBackups extends Command
{
    protected $signature = 'backup:clean';

    protected $description = 'Remove database backup files older than the configured retention period';

    public function handle(SettingsService $settingsService): int
    {
        $settings = $settingsService->getBackupSettings();
        $retentionDays = (int) ($settings['backup_retention_days'] ?? 30);

        $backupDir = storage_path('app/backups');

        if (! is_dir($backupDir)) {
            $this->info('No backups directory found.');

            return Command::SUCCESS;
        }

        $files = collect(scandir($backupDir))
            ->filter(fn ($f) => str_ends_with((string) $f, '.sql'))
            ->sort()
            ->map(fn ($f) => $backupDir.DIRECTORY_SEPARATOR.$f)
            ->values();

        $cutoff = now()->subDays($retentionDays);
        $deleted = 0;
        $kept = 0;

        foreach ($files as $file) {
            $lastModified = filemtime($file);

            if ($lastModified && now()->createFromTimestamp($lastModified)->lt($cutoff)) {
                unlink($file);
                $deleted++;

                $this->line('Deleted old backup: '.basename($file));
            } else {
                $kept++;
            }
        }

        $this->info("Cleaned {$deleted} old backup(s). Kept {$kept} backup(s).");

        if ($deleted > 0) {
            Log::info('Old backups cleaned', [
                'deleted' => $deleted,
                'kept' => $kept,
                'retention_days' => $retentionDays,
            ]);
        }

        return Command::SUCCESS;
    }
}
