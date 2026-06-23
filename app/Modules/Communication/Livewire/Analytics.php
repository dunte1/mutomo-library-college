<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\CommunicationAnalytic;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageRecipient;
use App\Modules\Communication\Services\MessagingService;
use Livewire\Component;

class Analytics extends Component
{
    public string $period = '30';

    public array $stats = [];

    public array $trends = [];

    public function mount(MessagingService $messagingService): void
    {
        $this->stats = $messagingService->getMessageStats();
        $this->loadTrends();
    }

    public function updatedPeriod(): void
    {
        $this->loadTrends();
    }

    public function loadTrends(): void
    {
        $days = (int) $this->period;
        $since = now()->subDays($days);

        $this->trends = [
            'daily_sent' => Message::sent()
                ->where('created_at', '>=', $since)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
            'by_type' => Message::sent()
                ->where('created_at', '>=', $since)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => Message::sent()
                ->where('created_at', '>=', $since)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
            'read_rate' => [
                'read' => MessageRecipient::where('is_read', true)
                    ->where('created_at', '>=', $since)
                    ->count(),
                'total' => MessageRecipient::where('created_at', '>=', $since)
                    ->count(),
            ],
            'events' => CommunicationAnalytic::where('created_at', '>=', $since)
                ->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type')
                ->toArray(),
        ];
    }

    public function render()
    {
        return view('communication::livewire.analytics', [
            'stats' => $this->stats,
            'trends' => $this->trends,
        ]);
    }
}
