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

        $filePath = storage_path("app/public/{$asset->file_path}");

        abort_if(! file_exists($filePath), 404);

        $asset->incrementViews();

        return response()->file($filePath, [
            'Content-Type' => $asset->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }
}
