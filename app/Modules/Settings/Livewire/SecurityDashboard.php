<?php

namespace App\Modules\Settings\Livewire;

use App\Models\LoginLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SecurityDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventType = '';

    protected $queryString = ['search', 'eventType'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $totalUsers = User::count();
        $activeToday = LoginLog::whereDate('login_at', today())->where('is_successful', true)->count();
        $failedToday = LoginLog::whereDate('login_at', today())->where('is_successful', false)->count();
        $twoFactorEnabled = User::where('two_factor_enabled', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        $recentActivity = LoginLog::with('user')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->eventType, fn ($q) => $q->where('is_successful', $this->eventType === 'success'))
            ->orderBy('login_at', 'desc')
            ->paginate(25);

        return view('settings::livewire.security-dashboard', [
            'totalUsers' => $totalUsers,
            'activeToday' => $activeToday,
            'failedToday' => $failedToday,
            'twoFactorEnabled' => $twoFactorEnabled,
            'inactiveUsers' => $inactiveUsers,
            'recentActivity' => $recentActivity,
        ]);
    }
}
