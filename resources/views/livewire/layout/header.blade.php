<?php

use Livewire\Volt\Component;
use App\Livewire\Actions\Logout;

new class extends Component
{
    public int $unreadCount = 0;
    public array $recentNotifications = [];

    public function mount(): void
    {
        $service = app(\App\Modules\Notifications\Services\NotificationService::class);
        $this->unreadCount = $service->getUnreadCount(auth()->user());
        $this->recentNotifications = $service->getNotifications(auth()->user(), 5)
            ->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'action_url' => $n->action_url,
                'is_read' => $n->is_read,
                'time_ago' => $n->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function markAllNotificationsRead(): void
    {
        app(\App\Modules\Notifications\Services\NotificationService::class)
            ->markAllAsRead(auth()->user());
        $this->unreadCount = 0;
        foreach ($this->recentNotifications as &$n) {
            $n['is_read'] = true;
        }
        $this->dispatch('notifications-updated', count: 0);
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<header class="h-16 bg-gradient-to-r from-primary-600 via-primary-700 to-primary-800 flex items-center justify-between px-4 md:px-6 shadow-soft">
    <div class="flex items-center gap-3">
        <button @click="$dispatch('toggle-sidebar')" class="md:hidden p-2 rounded-lg hover:bg-white/10 text-primary-200" aria-label="Toggle navigation menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <button @click="$dispatch('open-global-search'); Livewire.dispatch('openGlobalSearch')"
            class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm rounded-xl bg-white/10 border border-white/10 text-white/70 hover:text-white hover:bg-white/15 transition-colors w-64 backdrop-blur-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="flex-1 text-left">Search books, members...</span>
            <kbd class="hidden lg:inline-flex px-1.5 py-0.5 text-xs rounded bg-white/10 text-white/50">Ctrl+K</kbd>
        </button>
    </div>

    <div class="flex items-center gap-2">
        <button @click="$dispatch('toggle-dark-mode')"
            class="p-2 rounded-lg hover:bg-white/10 text-primary-200" aria-label="Toggle dark mode">
            <svg x-show="!document.documentElement.classList.contains('dark')" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg x-show="document.documentElement.classList.contains('dark')" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-white/10 text-primary-200" aria-label="Notifications">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold leading-none px-1">{{ $unreadCount }}</span>
                @endif
            </button>

            <div x-show="open" @click.away="open = false"
                 class="absolute right-0 mt-2 w-80 sm:w-96 card py-2 shadow-soft-lg z-50 max-h-[70vh] flex flex-col">
                <div class="flex items-center justify-between px-4 py-2 border-b border-surface-100 dark:border-surface-700">
                    <h3 class="text-sm font-semibold text-surface-900 dark:text-white">Notifications</h3>
                    <a href="{{ route('notifications.index') }}" wire:navigate class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                </div>
                <div class="overflow-y-auto flex-1">
                    @forelse($recentNotifications as $notif)
                        <a href="{{ $notif['action_url'] ?? route('notifications.index') }}" wire:navigate
                           class="flex items-start gap-3 px-4 py-3 hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors
                                  {{ !$notif['is_read'] ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                {{ $notif['is_read'] ? 'bg-surface-200 dark:bg-surface-700' : 'bg-primary-100 dark:bg-primary-900/30' }}">
                                <svg class="w-4 h-4 {{ $notif['is_read'] ? 'text-surface-500' : 'text-primary-600 dark:text-primary-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white truncate">{{ $notif['title'] }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400 truncate">{{ $notif['body'] }}</p>
                                <p class="text-xs text-surface-400 mt-0.5">{{ $notif['time_ago'] }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-sm text-surface-500 dark:text-surface-400">No notifications</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-surface-100 dark:border-surface-700 px-4 py-2">
                    <button wire:click="markAllNotificationsRead"
                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline w-full text-center">
                        Mark all as read
                    </button>
                </div>
            </div>
        </div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-white/10" aria-label="User menu">
                @php $avatar = auth()->user()->avatar ? url('storage/' . auth()->user()->avatar) : null; @endphp
                @if ($avatar)
                    <div class="w-8 h-8 rounded-xl overflow-hidden">
                        <img src="{{ $avatar }}" alt="{{ auth()->user()->name }}" loading="lazy" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white text-sm font-semibold">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                @endif
                <div class="hidden md:block text-left">
                    <p class="text-sm font-medium text-white leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-primary-200">{{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
                </div>
                <svg class="w-4 h-4 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" class="absolute right-0 sm:right-auto mt-2 w-56 card py-1 shadow-soft-lg z-50">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Profile
                </a>
                <hr class="border-surface-100 dark:border-surface-700 my-1">
                <button wire:click="logout" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </div>
</header>
