<?php

namespace App\Modules\Shared\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Model;

    public function findOrFail(int $id, array $columns = ['*']): Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;

    public function restore(int $id): bool;

    public function forceDelete(int $id): bool;

    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection;

    public function findWhere(array $conditions, array $columns = ['*']): Collection;

    public function count(): int;

    public function exists(array $conditions): bool;
}
