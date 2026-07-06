<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\CategoryResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\Category;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $categories = Category::with(['children'])
            ->active()
            ->parents()
            ->withCount('books')
            ->orderBy('sort_order')
            ->get();

        return $this->response->success(CategoryResource::collection($categories));
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $category = Category::with(['children', 'parent'])
            ->withCount('books')
            ->findOrFail($id);

        return $this->response->success(new CategoryResource($category));
    }
}
