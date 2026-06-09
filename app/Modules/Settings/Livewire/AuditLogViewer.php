<?php

namespace App\Modules\Settings\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $searchUser = '';
    public string $event = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $queryString = ['searchUser', 'event', 'dateFrom', 'dateTo'];

    public function clearOldLogs(): void
    {
        $cutoff = now()->subDays(90);

        Activity::where('created_at', '<', $cutoff)->delete();

        session()->flash('success', 'Logs older than 90 days have been cleared.');
    }

    public function render()
    {
        $logs = Activity::with('causer')
            ->when($this->searchUser, function ($q) {
                $q->whereHas('causer', function ($q) {
                    $q->where('name', 'like', "%{$this->searchUser}%")
                      ->orWhere('email', 'like', "%{$this->searchUser}%");
                });
            })
            ->when($this->event, fn ($q) => $q->where('event', $this->event))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(50);

        return view('settings::livewire.audit-log-viewer', [
            'logs' => $logs,
            'events' => Activity::select('event')->distinct()->pluck('event')->filter()->values(),
        ]);
    }
}
