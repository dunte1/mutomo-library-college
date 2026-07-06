<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\Recommendation;
use App\Modules\DigitalLibrary\Services\RecommendationEngine;
use App\Modules\Catalog\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class RecommendationController extends Controller
{
    public function __construct(
        protected RecommendationEngine $engine,
        protected ApiResponseService $response,
    ) {}

    public function index(): JsonResponse
    {
        $user = auth()->user();

        // Fetch persisted recommendations from DB
        $dbRecs = Recommendation::with(['book.authors', 'digitalAsset'])
            ->forUser($user->id)
            ->active()
            ->top(10)
            ->get();

        // If no recommendations exist, generate fresh ones
        if ($dbRecs->isEmpty()) {
            $freshRecs = $this->engine->generateForUser($user, 10);
            $normalized = $this->normalizeFromEngine($freshRecs);
        } else {
            $normalized = $this->normalizeFromModels($dbRecs);
        }

        // Get predictive overdue alert
        $alert = $this->engine->predictiveOverdueAlert($user);

        return $this->response->success($normalized, extra: $alert ? [
            'meta' => ['overdue_alert' => $alert],
        ] : []);
    }

    /**
     * Normalize Recommendation model collection to uniform response format.
     */
    protected function normalizeFromModels(iterable $recommendations): array
    {
        return collect($recommendations)->map(function (Recommendation $rec) {
            $item = $rec->book ?? $rec->digitalAsset;
            $image = null;

            if ($rec->book && $rec->book->cover_image) {
                $image = url('storage/'.$rec->book->cover_image);
            } elseif ($rec->digitalAsset && $rec->digitalAsset->cover_image) {
                $image = url('storage/'.$rec->digitalAsset->cover_image);
            }

            return [
                'type' => $rec->type,
                'item' => $rec->book ? [
                    'id' => $rec->book->id,
                    'title' => $rec->book->title,
                    'type' => 'book',
                    'cover_image' => $image,
                    'authors' => $rec->book->relationLoaded('authors')
                        ? $rec->book->authors->map(fn ($a) => ['name' => $a->name])
                        : [],
                ] : ($rec->digitalAsset ? [
                    'id' => $rec->digitalAsset->id,
                    'title' => $rec->digitalAsset->title,
                    'type' => 'digital_asset',
                    'file_type' => $rec->digitalAsset->file_type,
                    'cover_image' => $image,
                ] : null),
                'reason' => $rec->reason,
                'score' => (float) $rec->score,
            ];
        })->filter(fn ($r) => $r['item'] !== null)->values()->toArray();
    }

    /**
     * Normalize engine-generated array recommendations to uniform response format.
     */
    protected function normalizeFromEngine(array $recommendations): array
    {
        return collect($recommendations)->map(function (array $rec) {
            $book = isset($rec['book_id']) ? Book::with('authors')->find($rec['book_id']) : null;
            $asset = isset($rec['digital_asset_id']) ? DigitalAsset::find($rec['digital_asset_id']) : null;

            return [
                'type' => $rec['type'],
                'item' => $book ? [
                    'id' => $book->id,
                    'title' => $book->title,
                    'type' => 'book',
                    'cover_image' => $book->cover_image ? url('storage/'.$book->cover_image) : null,
                    'authors' => $book->relationLoaded('authors')
                        ? $book->authors->map(fn ($a) => ['name' => $a->name])
                        : [],
                ] : ($asset ? [
                    'id' => $asset->id,
                    'title' => $asset->title,
                    'type' => 'digital_asset',
                    'file_type' => $asset->file_type,
                    'cover_image' => $asset->cover_image ? url('storage/'.$asset->cover_image) : null,
                ] : null),
                'reason' => $rec['reason'],
                'score' => (float) ($rec['score'] ?? 0),
            ];
        })->filter(fn ($r) => $r['item'] !== null)->values()->toArray();
    }
}
