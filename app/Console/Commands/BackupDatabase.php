<?php

namespace App\Console\Commands;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
        {--force : Run backup even if auto_backup is disabled in settings}';

    protected $description = 'Create a database backup dump file';

    public function handle(SettingsService $settingsService): int
    {
        $settings = $settingsService->getBackupSettings();

        if (!($settings['auto_backup'] ?? true) && !$this->option('force')) {
            $this->info('Automatic backups are disabled in settings. Use --force to override.');

            return Command::SUCCESS;
        }

        $this->info('Creating database backup...');

        try {
            $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $db = config('database.default');
            $config = config("database.connections.{$db}");

            if ($db === 'sqlite') {
                $databasePath = $config['database'];
                if ($databasePath === ':memory:') {
                    $this->error('Cannot backup in-memory SQLite database.');

                    return Command::FAILURE;
                }
                copy($databasePath, $path);
                $this->line("Copied SQLite database: {$databasePath}");
            } elseif (in_array($db, ['mysql', 'mariadb'], true)) {
                $cnfPath = tempnam(sys_get_temp_dir(), 'mycnf_');
                $cnfContent = sprintf(
                    "[client]%shost=%s%sport=%s%suser=%s%spassword=%s%s",
                    PHP_EOL,
                    $config['host'] ?? '127.0.0.1',
                    PHP_EOL,
                    $config['port'] ?? '3306',
                    PHP_EOL,
                    $config['username'] ?? 'root',
                    PHP_EOL,
                    $config['password'] ?? '',
                    PHP_EOL
                );
                file_put_contents($cnfPath, $cnfContent, LOCK_EX);
                chmod($cnfPath, 0600);

                $cmd = sprintf(
                    'mysqldump --defaults-extra-file=%s %s > %s 2>&1',
                    escapeshellarg($cnfPath),
                    escapeshellarg($config['database']),
                    escapeshellarg($path)
                );
                exec($cmd, $output, $exitCode);
                unlink($cnfPath);

                if ($exitCode !== 0) {
                    $outputText = implode("\n", $output);
                    throw new \RuntimeException("mysqldump failed (exit {$exitCode}): {$outputText}");
                }

                $this->line("Dumped MySQL database: {$config['database']}");
            } else {
                $this->error("Backup not supported for '{$db}' database driver.");

                return Command::FAILURE;
            }

            $size = file_exists($path) ? round(filesize($path) / 1024 / 1024, 2) . ' MB' : '0 B';
            $this->info("Backup created successfully: {$filename} ({$size})");

            Log::info('Database backup completed', [
                'filename' => $filename,
                'size' => $size,
                'driver' => $db,
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Backup failed: {$e->getMessage()}");

            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'driver' => config('database.default'),
            ]);

            return Command::FAILURE;
        }
    }
}
