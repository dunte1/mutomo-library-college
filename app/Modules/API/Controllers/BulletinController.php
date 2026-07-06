<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Communication\Models\Bulletin;
use Illuminate\Routing\Controller;

class BulletinController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $bulletins = Bulletin::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->limit(20)
            ->get()
            ->map(fn ($b) => $this->format($b));

        return $this->response->success($bulletins);
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $bulletin = Bulletin::where('status', 'published')->findOrFail($id);
        return $this->response->success($this->format($bulletin));
    }

    protected function format(Bulletin $bulletin): array
    {
        return [
            'id' => $bulletin->id,
            'title' => $bulletin->title,
            'content' => $bulletin->content,
            'status' => $bulletin->status,
            'published_at' => $bulletin->published_at?->toIso8601String(),
            'created_at' => $bulletin->created_at?->toIso8601String(),
            'author' => $bulletin->creator ? [
                'name' => $bulletin->creator->name,
            ] : null,
        ];
    }
}
