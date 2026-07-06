<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\DigitalAssetResource;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class DigitalAssetController extends Controller
{
    public function index(): JsonResponse
    {
        $assets = DigitalAsset::active()
            ->when(request('type'), fn ($q) => $q->where('file_type', request('type')))
            ->when(request('category'), fn ($q) => $q->where('category_id', request('category')))
            ->when(request('search'), fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', '%'.request('search').'%')
                    ->orWhere('author', 'like', '%'.request('search').'%');
            }))
            ->paginate(request('per_page', 15));

        return response()->json($assets);
    }

    public function show(DigitalAsset $asset): JsonResponse
    {
        $asset->load('category');
        $asset->incrementViews();

        return response()->json(['data' => new DigitalAssetResource($asset)]);
    }

    public function download(DigitalAsset $asset): JsonResponse
    {
        if (! $asset->allow_download) {
            return response()->json(['message' => 'Download not allowed for this asset.'], 403);
        }

        $asset->incrementDownloads();

        return response()->json([
            'url' => url('storage/'.$asset->file_path),
            'filename' => $asset->slug.'.'.$asset->file_extension,
        ]);
    }
}
