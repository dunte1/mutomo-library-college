<?php

namespace App\Modules\Members\Repositories;

use App\Modules\Members\Models\Member;
use App\Modules\Shared\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberRepository implements BaseRepositoryInterface
{
    public function __construct(protected Member $model) {}

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->with(['registeredBy'])->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->with(['registeredBy'])->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->with(['registeredBy'])->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->with(['registeredBy'])->findOrFail($id, $columns);
    }

    public function findByMemberId(string $memberId): ?Member
    {
        return $this->model->where('member_id', $memberId)->first();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $member = $this->findOrFail($id);
        $member->update($data);

        return $member;
    }

    public function updateMember(Member $member, array $data): Member
    {
        $member->update($data);

        return $member;
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function deleteMember(Member $member): bool
    {
        return $member->delete();
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

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('member_id', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('id_number', 'like', "%{$query}%")
                ->orWhere('admission_number', 'like', "%{$query}%");
        })->with(['registeredBy'])->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return $this->model->active()->with(['registeredBy'])->get();
    }

    public function getByType(string $type): Collection
    {
        return $this->model->where('membership_type', $type)->with(['registeredBy'])->get();
    }

    public function getExpired(): Collection
    {
        return $this->model->expired()->with(['registeredBy'])->get();
    }

    public function getOverdueBorrowers(): Collection
    {
        return $this->model->whereHas('borrowRecords', function ($q) {
            $q->where('status', 'overdue');
        })->with(['borrowRecords' => function ($q) {
            $q->where('status', 'overdue');
        }])->get();
    }
}
