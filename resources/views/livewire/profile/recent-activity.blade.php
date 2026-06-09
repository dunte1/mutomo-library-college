<?php

use App\Models\User;
use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;

new class extends Component
{
    public array $activities = [];

    public function mount(): void
    {
        $this->activities = Activity::causedBy(auth()->user())
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($log) => [
                'description' => $log->description,
                'event' => $log->event,
                'subject' => $log->subject_type ? class_basename($log->subject_type) : null,
                'properties' => $log->properties->toArray(),
                'ip' => $log->getExtraProperty('ip') ?? 'N/A',
                'time' => $log->created_at->diffForHumans(),
            ])
            ->toArray();
    }
}; ?>

<section class="card">
    <div class="card-header">
        <h3 class="font-semibold text-surface-900 dark:text-white">Recent Activity</h3>
    </div>
    <div class="card-body">
        @forelse($activities as $activity)
            <div class="flex items-start gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                <div class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-surface-900 dark:text-white">{{ $activity['description'] }}</p>
                    <p class="text-xs text-surface-500">
                        @if($activity['subject'])
                            {{ $activity['subject'] }} &middot;
                        @endif
                        IP: {{ $activity['ip'] }} &middot; {{ $activity['time'] }}
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-surface-300 dark:text-surface-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-surface-500">No recent activity recorded.</p>
            </div>
        @endforelse
    </div>
</section>
