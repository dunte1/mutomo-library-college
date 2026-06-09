<?php

namespace App\Modules\Finance\Services;

use App\Models\User;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Finance\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getDashboardAnalytics(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));

        return [
            'borrow_trends' => $this->borrowTrends(6),
            'fine_trends' => $this->fineTrends(6),
            'collection_trends' => $this->collectionTrends(6),
            'overdue_rate' => $this->overdueRate(),
            'top_borrowers' => $this->topBorrowers(5),
            'peak_hours' => $this->peakBorrowHours(),
            'department_usage' => $this->departmentUsage(),
            'popular_categories' => $this->popularCategories(5),
        ];
    }

    public function borrowTrends(int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $data[$date->format('M Y')] = BorrowRecord::whereBetween('borrowed_at', [$start, $end])->count();
        }
        return $data;
    }

    public function fineTrends(int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $data[$date->format('M Y')] = Fine::whereBetween('created_at', [$start, $end])->sum('amount');
        }
        return $data;
    }

    public function collectionTrends(int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $data[$date->format('M Y')] = Transaction::completed()
                ->whereBetween('paid_at', [$start, $end])
                ->sum('amount');
        }
        return $data;
    }

    public function overdueRate(): float
    {
        $total = BorrowRecord::count();
        if ($total === 0) return 0;
        return round((BorrowRecord::overdue()->count() / $total) * 100, 1);
    }

    public function topBorrowers(int $limit = 5): array
    {
        return User::withCount('borrowRecords')
            ->orderByDesc('borrow_records_count')
            ->limit($limit)
            ->get()
            ->map(fn ($u) => ['name' => $u->name, 'count' => $u->borrow_records_count])
            ->toArray();
    }

    public function peakBorrowHours(): array
    {
        $driver = DB::connection()->getDriverName();

        $hourExpr = match ($driver) {
            'mysql' => "HOUR(borrowed_at)",
            'pgsql' => "EXTRACT(HOUR FROM borrowed_at)",
            'sqlite' => "strftime('%H', borrowed_at)",
            default => "strftime('%H', borrowed_at)",
        };

        $hours = BorrowRecord::selectRaw("{$hourExpr} as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        return $hours;
    }

    public function departmentUsage(): array
    {
        $driver = DB::connection()->getDriverName();

        $groupBy = match ($driver) {
            'pgsql' => 'department_id',
            default => 'department_id',
        };

        return User::select('department_id', DB::raw('COUNT(*) as borrow_count'))
            ->whereHas('borrowRecords')
            ->with('department:id,name')
            ->groupBy(DB::raw($groupBy))
            ->get()
            ->map(fn ($u) => [
                'department' => $u->department?->name ?? 'Unknown',
                'count' => $u->borrow_count,
            ])
            ->toArray();
    }

    public function popularCategories(int $limit = 5): array
    {
        return \App\Modules\Catalog\Models\Category::withCount(['books as borrow_count' => function ($q) {
            $q->whereHas('copies.borrowRecords');
        }])
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get()
            ->map(fn ($c) => ['name' => $c->name, 'count' => $c->borrow_count])
            ->toArray();
    }
}
