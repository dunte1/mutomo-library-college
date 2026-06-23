<?php

namespace App\Modules\Settings\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class SystemLogViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $event = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected $queryString = ['search', 'event', 'dateFrom', 'dateTo'];

    public function clearLogs(): void
    {
        $this->authorize('clear-audit-logs');

        Activity::truncate();

        $this->dispatch('notify', message: 'All system logs have been cleared.', type: 'success');
    }

    public function render()
    {
        $logs = Activity::with('causer')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('description', 'like', "%{$this->search}%")
                        ->orWhereHas('causer', function ($q) {
                            $q->where('name', 'like', "%{$this->search}%")
                                ->orWhere('email', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->event, fn ($q) => $q->where('event', $this->event))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(50);

        return view('settings::livewire.system-log-viewer', [
            'logs' => $logs,
            'events' => Activity::select('event')->distinct()->pluck('event')->filter()->values(),
        ]);
    }
}
