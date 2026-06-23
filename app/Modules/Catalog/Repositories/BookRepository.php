<?php

namespace App\Modules\Catalog\Repositories;

use App\Modules\Catalog\Models\Book;
use App\Modules\Shared\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BookRepository implements BaseRepositoryInterface
{
    public function __construct(protected Book $model) {}

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->with(['authors', 'publisher', 'category', 'copies'])->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->with(['authors', 'publisher', 'category'])
            ->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->with(['authors', 'publisher', 'category', 'subjects', 'copies'])
            ->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->with(['authors', 'publisher', 'category', 'subjects', 'copies', 'digitalAssets'])
            ->findOrFail($id, $columns);
    }

    public function create(array $data): Model
    {
        $book = $this->model->create($data);

        if (isset($data['authors'])) {
            $book->authors()->sync($data['authors']);
        }

        if (isset($data['subjects'])) {
            $book->subjects()->sync($data['subjects']);
        }

        return $book->load(['authors', 'publisher', 'category', 'subjects']);
    }

    public function update(int $id, array $data): Model
    {
        $book = $this->findOrFail($id);
        $book->update($data);

        if (isset($data['authors'])) {
            $book->authors()->sync($data['authors']);
        }

        if (isset($data['subjects'])) {
            $book->subjects()->sync($data['subjects']);
        }

        return $book->load(['authors', 'publisher', 'category', 'subjects']);
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function restore(int $id): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete(int $id): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->forceDelete();
    }

    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model->where($field, $value)->get($columns);
    }

    public function findWhere(array $conditions, array $columns = ['*']): Collection
    {
        return $this->model->where($conditions)->get($columns);
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function exists(array $conditions): bool
    {
        return $this->model->where($conditions)->exists();
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->search($term)
            ->with(['authors', 'publisher', 'category'])
            ->paginate($perPage);
    }

    public function searchWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['authors', 'publisher', 'category']);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (! empty($filters['author_id'])) {
            $query->byAuthor($filters['author_id']);
        }

        if (! empty($filters['publisher_id'])) {
            $query->byPublisher($filters['publisher_id']);
        }

        if (! empty($filters['subject_id'])) {
            $query->bySubject($filters['subject_id']);
        }

        if (! empty($filters['year'])) {
            $query->byYear($filters['year']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['language'])) {
            $query->where('language', $filters['language']);
        }

        $allowedSortFields = ['title', 'isbn', 'publication_year', 'pages', 'language', 'created_at', 'edition'];
        $sortField = in_array($filters['sort'] ?? 'title', $allowedSortFields) ? $filters['sort'] : 'title';
        $sortDir = in_array(strtolower($filters['direction'] ?? 'asc'), ['asc', 'desc']) ? strtolower($filters['direction']) : 'asc';
        $query->orderBy($sortField, $sortDir);

        return $query->paginate($perPage);
    }
}
