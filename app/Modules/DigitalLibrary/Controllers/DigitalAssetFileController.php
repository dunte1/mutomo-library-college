<?php

namespace App\Modules\DigitalLibrary\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use Illuminate\Support\Facades\Gate;

class DigitalAssetFileController extends Controller
{
    public function show(DigitalAsset $asset)
    {
        Gate::authorize('view-digital-assets');

        abort_if(! $asset->is_active, 404);

        abort_if($this->isUnsafePath($asset->file_path), 403);

        $filePath = storage_path("app/public/{$asset->file_path}");

        abort_if(! file_exists($filePath), 404);

        $asset->incrementViews();

        return response()->file($filePath, [
            'Content-Type' => $asset->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }

    private function isUnsafePath(string $path): bool
    {
        if (str_contains($path, '..')) {
            return true;
        }

        $resolved = realpath(storage_path("app/public/{$path}"));
        $allowed = realpath(storage_path('app/public'));

        return $resolved === false || $allowed === false || ! str_starts_with($resolved, $allowed);
    }
}
