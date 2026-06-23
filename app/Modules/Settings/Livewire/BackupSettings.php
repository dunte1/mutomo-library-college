<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class BackupSettings extends Component
{
    public array $settings = [];

    public bool $saved = false;

    public bool $backingUp = false;

    public ?string $backupResult = null;

    public bool $backupSuccess = false;

    public ?string $lastBackupDate = null;

    public ?string $lastBackupSize = null;

    protected $rules = [
        'settings.auto_backup' => 'boolean',
        'settings.backup_frequency' => 'required|string|in:daily,weekly,monthly',
        'settings.backup_retention_days' => 'required|integer|min:1|max:365',
        'settings.backup_location' => 'required|string|in:local,s3,dropbox,gcs',
    ];

    public function mount(): void
    {
        $this->settings = app(SettingsService::class)->getBackupSettings();
        $this->loadBackupInfo();
    }

    public function save(): void
    {
        $this->validate();

        app(SettingsService::class)->updateSettings('backup', $this->settings);

        $this->saved = true;
        session()->flash('success', 'Backup settings saved successfully.');
    }

    public function createBackup(): void
    {
        $this->backingUp = true;
        $this->backupResult = null;

        try {
            $filename = 'backup-'.now()->format('Y-m-d-H-i-s').'.sql';
            $path = storage_path('app/backups/'.$filename);

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $db = config('database.default');
            $config = config("database.connections.{$db}");

            if ($db === 'sqlite') {
                copy($config['database'], $path);
            } elseif (in_array($db, ['mysql', 'mariadb'], true)) {
                $cnfPath = tempnam(sys_get_temp_dir(), 'mycnf_');
                $cnfContent = sprintf(
                    "[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n",
                    $config['host'],
                    $config['port'],
                    $config['username'],
                    $config['password']
                );
                file_put_contents($cnfPath, $cnfContent, LOCK_EX);
                chmod($cnfPath, 0600);

                $cmd = sprintf(
                    'mysqldump --defaults-extra-file=%s %s > %s',
                    escapeshellarg($cnfPath),
                    escapeshellarg($config['database']),
                    escapeshellarg($path)
                );
                exec($cmd, $output, $exitCode);
                unlink($cnfPath);
                if ($exitCode !== 0) {
                    throw new \RuntimeException('mysqldump failed.');
                }
            } else {
                throw new \RuntimeException("Backup not supported for {$db} driver.");
            }

            $this->backupSuccess = true;
            $this->backupResult = 'Backup created successfully: '.$filename;
            $this->lastBackupDate = now()->format('d M Y H:i');
            $this->lastBackupSize = file_exists($path) ? round(filesize($path) / 1024 / 1024, 2).' MB' : null;
        } catch (\Throwable $e) {
            $this->backupSuccess = false;
            $this->backupResult = 'Backup failed: '.$e->getMessage();
        }

        $this->backingUp = false;
    }

    protected function loadBackupInfo(): void
    {
        $disk = Storage::disk('local');
        $backupDir = 'backups';

        if ($disk->exists($backupDir)) {
            $files = collect($disk->files($backupDir))->sortDesc();

            if ($files->isNotEmpty()) {
                $latest = $files->first();
                $this->lastBackupDate = $disk->lastModified($latest)
                    ? now()->createFromTimestamp($disk->lastModified($latest))->format('d M Y H:i')
                    : null;
                $this->lastBackupSize = $disk->size($latest)
                    ? round($disk->size($latest) / 1024 / 1024, 2).' MB'
                    : null;
            }
        }
    }

    public function render()
    {
        return view('settings::livewire.backup-settings');
    }
}
