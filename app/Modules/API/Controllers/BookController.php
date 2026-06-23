<?php

namespace App\Modules\API\Controllers;

use App\Modules\Catalog\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        $books = Book::with(['authors', 'category', 'publisher'])
            ->when(request('search'), fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', '%'.request('search').'%')
                    ->orWhere('isbn', 'like', '%'.request('search').'%');
            }))
            ->when(request('category'), fn ($q) => $q->whereHas('category', fn ($q) => $q->where('slug', request('category'))))
            ->when(request('author'), fn ($q) => $q->whereHas('authors', fn ($q) => $q->where('id', request('author'))))
            ->paginate(request('per_page', 15));

        return response()->json($books);
    }

    public function show(Book $book): JsonResponse
    {
        $book->load(['authors', 'category', 'subjects', 'publisher', 'copies' => fn ($q) => $q->with('currentBorrow')]);

        return response()->json(['data' => $book]);
    }

    public function search(): JsonResponse
    {
        $query = request('q');

        if (! $query || strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $books = Book::where('title', 'like', "%{$query}%")
            ->orWhere('isbn', 'like', "%{$query}%")
            ->orWhereHas('authors', fn ($q) => $q->where('name', 'like', "%{$query}%"))
            ->limit(20)
            ->get();

        return response()->json(['data' => $books]);
    }
}
