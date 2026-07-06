<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\ReadingHistory;
use Illuminate\Routing\Controller;

class ReadingHistoryController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $history = ReadingHistory::with('digitalAsset')
            ->forUser(auth()->id())
            ->latest('started_at')
            ->paginate(min((int) ($data['per_page'] ?? 15), 100));

        $history->getCollection()->transform(fn ($h) => [
            'asset' => $h->digitalAsset ? [
                'id' => $h->digitalAsset->id,
                'title' => $h->digitalAsset->title,
                'file_type' => $h->digitalAsset->file_type,
                'cover_image' => $h->digitalAsset->cover_image
                    ? url('storage/'.$h->digitalAsset->cover_image)
                    : null,
            ] : null,
            'progress' => $h->progress,
            'last_page' => $h->last_page,
            'started_at' => $h->started_at?->toIso8601String(),
            'completed_at' => $h->completed_at?->toIso8601String(),
        ]);

        return $this->response->paginated($history);
    }

    public function update(int $assetId): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'last_page' => ['nullable', 'integer', 'min:0'],
        ]);

        $asset = DigitalAsset::findOrFail($assetId);

        $history = ReadingHistory::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'digital_asset_id' => $asset->id,
            ],
            [
                'progress' => $data['progress'],
                'last_page' => $data['last_page'] ?? null,
                'completed_at' => $data['progress'] >= 100 ? now() : null,
                'started_at' => now(),
            ]
        );

        return $this->response->success([
            'progress' => $history->progress,
            'last_page' => $history->last_page,
            'completed_at' => $history->completed_at?->toIso8601String(),
        ], 'Reading progress updated.');
    }
}
