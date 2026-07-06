<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\AuthorResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Catalog\Models\Author;
use Illuminate\Routing\Controller;

class AuthorController extends Controller
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

        $query = Author::active()->withCount('books');

        if (! empty($data['search'])) {
            $query->search($data['search']);
        }

        $authors = $query->orderBy('name')
            ->paginate(min((int) ($data['per_page'] ?? 50), 100));

        $authors->getCollection()->transform(fn ($a) => new AuthorResource($a));

        return $this->response->paginated($authors);
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $author = Author::withCount('books')->findOrFail($id);

        return $this->response->success(new AuthorResource($author));
    }
}
