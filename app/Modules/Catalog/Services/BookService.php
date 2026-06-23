<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Catalog\Repositories\BookRepository;
use App\Modules\Shared\Helpers\AuditHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class BookService
{
    public function __construct(
        protected BookRepository $bookRepository,
        protected BarcodeService $barcodeService,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->paginate($perPage);
    }

    public function find(int $id): Book
    {
        return $this->bookRepository->findOrFail($id);
    }

    public function create(array $data): Book
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        $book = $this->bookRepository->create($data);

        if (! empty($data['copies_count'])) {
            for ($i = 0; $i < $data['copies_count']; $i++) {
                $book->copies()->create([
                    'barcode' => $this->barcodeService->generate(),
                    'shelf_location' => $data['shelf_location'] ?? null,
                    'status' => BookCopy::STATUS_AVAILABLE,
                    'condition' => 'new',
                    'acquired_at' => now(),
                    'price' => $data['price'] ?? null,
                ]);
            }
        }

        AuditHelper::log('created', 'catalog', [
            'book_id' => $book->id,
            'title' => $book->title,
        ]);

        return $book;
    }

    public function update(int $id, array $data): Book
    {
        if (isset($data['title'])) {
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        }

        $book = $this->bookRepository->update($id, $data);

        AuditHelper::log('updated', 'catalog', [
            'book_id' => $book->id,
            'title' => $book->title,
        ]);

        return $book;
    }

    public function delete(int $id): bool
    {
        $book = $this->bookRepository->findOrFail($id);

        AuditHelper::log('deleted', 'catalog', [
            'book_id' => $book->id,
            'title' => $book->title,
        ]);

        return $this->bookRepository->delete($id);
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->search($term, $perPage);
    }

    public function searchWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->searchWithFilters($filters, $perPage);
    }

    public function getStatistics(): array
    {
        $total = $this->bookRepository->count();
        $active = $this->bookRepository->findWhere(['is_active' => true])->count();
        $totalCopies = BookCopy::count();
        $availableCopies = BookCopy::where('status', BookCopy::STATUS_AVAILABLE)->count();
        $borrowedCopies = BookCopy::where('status', BookCopy::STATUS_BORROWED)->count();

        return compact('total', 'active', 'totalCopies', 'availableCopies', 'borrowedCopies');
    }
}
