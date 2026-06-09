<?php

use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
{
    public array $sessions = [];

    public function mount(): void
    {
        $this->loadSessions();
    }

    public function logoutOtherSessions(): void
    {
        auth()->logoutOtherDevices(request()->password ?? '');
        $this->dispatch('notify', message: 'Other sessions logged out.', type: 'success');
        $this->loadSessions();
    }

    protected function loadSessions(): void
    {
        $userId = auth()->id();
        $currentSession = session()->getId();

        $this->sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSession) {
                $payload = @unserialize(base64_decode($session->payload));
                return [
                    'id' => $session->id,
                    'is_current' => $session->id === $currentSession,
                    'user_agent' => $session->user_agent,
                    'ip_address' => $session->ip_address,
                    'last_activity' => now()->createFromTimestamp($session->last_activity)->diffForHumans(),
                    'device' => $this->parseDevice($session->user_agent),
                    'browser' => $this->parseBrowser($session->user_agent),
                ];
            })
            ->toArray();
    }

    protected function parseDevice(?string $ua): string
    {
        if (!$ua) return 'Unknown';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac')) return 'macOS';
        if (str_contains($ua, 'Linux')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Unknown';
    }

    protected function parseBrowser(?string $ua): string
    {
        if (!$ua) return 'Unknown';
        if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edg')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        if (str_contains($ua, 'Edg')) return 'Edge';
        if (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident')) return 'Internet Explorer';
        return 'Unknown';
    }
}; ?>

<section class="card">
    <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold text-surface-900 dark:text-white">Active Sessions</h3>
        @if(count($sessions) > 1)
            <button wire:click="logoutOtherSessions" class="btn-outline btn-sm text-xs">
                Logout Other Sessions
            </button>
        @endif
    </div>
    <div class="card-body">
        <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Manage your active sessions across devices.</p>

        <div class="space-y-2">
            @forelse($sessions as $session)
                <div class="flex items-center gap-4 p-3 rounded-xl {{ $session['is_current'] ? 'bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800' : 'bg-surface-50 dark:bg-surface-800/50' }}">
                    <div class="w-10 h-10 rounded-xl bg-surface-200 dark:bg-surface-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($session['device'] === 'Windows' || $session['device'] === 'Linux')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                            @elseif($session['device'] === 'macOS' || $session['device'] === 'iOS')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            @elseif($session['device'] === 'Android')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            @endif
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium {{ $session['is_current'] ? 'text-primary-700 dark:text-primary-300' : 'text-surface-900 dark:text-white' }}">
                            {{ $session['device'] }} &middot; {{ $session['browser'] }}
                            @if($session['is_current'])
                                <span class="text-xs text-primary-600 dark:text-primary-400">(Current session)</span>
                            @endif
                        </p>
                        <p class="text-xs text-surface-500">
                            IP: {{ $session['ip_address'] }} &middot; Last activity: {{ $session['last_activity'] }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-surface-500 text-center py-4">No active sessions found.</p>
            @endforelse
        </div>
    </div>
</section>
