<?php

namespace App\Modules\API\Controllers;

use App\Modules\Catalog\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        $data = request()->validate([
            'search' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'author' => 'sometimes|integer|exists:authors,id',
            'featured' => 'sometimes|boolean',
            'days' => 'sometimes|integer|min:1|max:365',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $books = Book::with(['authors', 'category', 'publisher'])
            ->when($data['search'] ?? null, fn ($q) => $q->where(function ($q) use ($data) {
                $q->whereFullText('title,description', $data['search'])
                    ->orWhere('isbn', 'like', '%'.$data['search'].'%');
            }))
            ->when($data['category'] ?? null, fn ($q) => $q->whereHas('category', fn ($q) => $q->where('slug', $data['category'])))
            ->when($data['category_id'] ?? null, fn ($q) => $q->where('category_id', $data['category_id']))
            ->when($data['author'] ?? null, fn ($q) => $q->whereHas('authors', fn ($q) => $q->where('id', $data['author'])))
            ->when(filled($data['featured'] ?? null), fn ($q) => $q->where('is_featured', true))
            ->when($data['days'] ?? null, fn ($q) => $q->where('created_at', '>=', now()->subDays((int)$data['days'])))
            ->orderBy('created_at', 'desc')
            ->paginate($data['per_page'] ?? 15);

        return response()->json($books);
    }

    public function show(Book $book): JsonResponse
    {
        $book->load(['authors', 'category', 'subjects', 'publisher', 'copies' => fn ($q) => $q->with('currentBorrow')]);

        return response()->json(['data' => $book]);
    }

    public function search(): JsonResponse
    {
        $data = request()->validate([
            'q' => 'required|string|min:2|max:255',
        ]);

        $books = Book::whereFullText('title,description', $data['q'])
            ->orWhere('isbn', 'like', "%{$data['q']}%")
            ->orWhereHas('authors', fn ($q) => $q->where('name', 'like', "%{$data['q']}%"))
            ->limit(20)
            ->get();

        return response()->json(['data' => $books]);
    }
}
