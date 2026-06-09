<?php

namespace App\Services;

use App\Models\DownloadLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadService
{
    protected int $rateLimit = 100;
    protected int $rateWindowMinutes = 60;

    public function allowDownload(Model $downloadable, string $permission, string $type = 'document'): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (!Gate::allows($permission)) {
            return false;
        }

        if ($this->isRateLimited($user->id)) {
            return false;
        }

        return true;
    }

    public function download(Model $downloadable, string $filePath, string $fileName, string $title, string $type = 'document', string $permission = null): BinaryFileResponse|StreamedResponse|null
    {
        $user = auth()->user();

        if ($permission && !Gate::allows($permission)) {
            abort(403, 'You do not have permission to download this file.');
        }

        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        if ($this->isRateLimited($user?->id)) {
            $this->logDownload($downloadable, $type, $title, true);
            abort(429, sprintf(
                'Too many downloads. Limit is %d per %d minutes.',
                $this->rateLimit,
                $this->rateWindowMinutes
            ));
        }

        $this->logDownload($downloadable, $type, $title);

        activity()
            ->performedOn($downloadable)
            ->causedBy($user)
            ->log("Downloaded: {$title}");

        return response()->download($filePath, $fileName);
    }

    public function isRateLimited(int $userId): bool
    {
        $count = DownloadLog::recentForUser($userId, $this->rateWindowMinutes)->count();
        return $count >= $this->rateLimit;
    }

    public function getRemainingDownloads(int $userId): int
    {
        $count = DownloadLog::recentForUser($userId, $this->rateWindowMinutes)->count();
        return max(0, $this->rateLimit - $count);
    }

    protected function logDownload(Model $downloadable, string $type, string $title, bool $wasThrottled = false): void
    {
        DownloadLog::create([
            'user_id' => auth()->id(),
            'downloadable_type' => get_class($downloadable),
            'downloadable_id' => $downloadable->id,
            'type' => $type,
            'title' => $title,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'was_throttled' => $wasThrottled,
        ]);
    }
}
