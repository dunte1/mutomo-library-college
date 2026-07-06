<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\PublisherResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\Publisher;
use Illuminate\Routing\Controller;

class PublisherController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Publisher::active()->withCount('books');

        if (! empty($data['search'])) {
            $query->search($data['search']);
        }

        $publishers = $query->orderBy('name')
            ->paginate(min((int) ($data['per_page'] ?? 50), 100));

        $publishers->getCollection()->transform(fn ($p) => new PublisherResource($p));

        return $this->response->paginated($publishers);
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $publisher = Publisher::withCount('books')->findOrFail($id);

        return $this->response->success(new PublisherResource($publisher));
    }
}
