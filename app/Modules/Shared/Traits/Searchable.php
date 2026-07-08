<?php

namespace App\Modules\Shared\Traits;

trait Searchable
{
    public function scopeWhereLike($query, string $column, string $term)
    {
        if ($query->getConnection()->getDriverName() === 'mysql') {
            return $query->whereRaw("{$column} LIKE ? ESCAPE '\\\\'", ["%{$term}%"]);
        }

        return $query->where($column, 'like', "%{$term}%");
    }

    public function scopeOrWhereLike($query, string $column, string $term)
    {
        if ($query->getConnection()->getDriverName() === 'mysql') {
            return $query->orWhereRaw("{$column} LIKE ? ESCAPE '\\\\'", ["%{$term}%"]);
        }

        return $query->orWhere($column, 'like', "%{$term}%");
    }

    public function scopeWhereFullText($query, string $columns, string $term)
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            return $query->whereRaw("MATCH({$columns}) AGAINST(? IN BOOLEAN MODE)", [$term . '*']);
        }

        $cols = explode(',', $columns);
        $query->where(function ($q) use ($cols, $term) {
            foreach ($cols as $col) {
                $col = trim($col);
                $q->orWhere($col, 'like', "%{$term}%");
            }
        });
    }

    public function scopeOrWhereFullText($query, string $columns, string $term)
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            return $query->orWhereRaw("MATCH({$columns}) AGAINST(? IN BOOLEAN MODE)", [$term . '*']);
        }

        $cols = explode(',', $columns);
        $query->orWhere(function ($q) use ($cols, $term) {
            foreach ($cols as $col) {
                $col = trim($col);
                $q->orWhere($col, 'like', "%{$term}%");
            }
        });
    }
}
