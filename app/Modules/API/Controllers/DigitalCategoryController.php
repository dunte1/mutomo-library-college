<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use Illuminate\Routing\Controller;

class DigitalCategoryController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $categories = DigitalAssetCategory::active()
            ->withCount('assets')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'assets_count' => $c->assets_count,
            ]);

        return $this->response->success($categories);
    }
}
