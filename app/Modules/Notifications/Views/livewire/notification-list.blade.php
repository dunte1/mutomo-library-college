@section('title', 'Notifications')
<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Notifications</h2>
            <p class="text-sm text-surface-500 dark:text-surface-400">{{ $unreadCount }} unread</p>
        </div>
        @if($unreadCount > 0)
            <button wire:click="markAllAsRead" class="btn-outline text-sm">
                Mark All as Read
            </button>
        @endif
    </div>

    <div class="space-y-1">
        @forelse($notifications as $notification)
            <div class="flex items-start gap-4 p-4 rounded-xl transition-colors
                {{ $notification->is_read ? 'bg-surface-50 dark:bg-surface-800/50' : 'bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800' }}"
                wire:key="notif-{{ $notification->id }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                    {{ $notification->is_read ? 'bg-surface-200 dark:bg-surface-700' : 'bg-primary-100 dark:bg-primary-900/30' }}">
                    <svg class="w-5 h-5 {{ $notification->is_read ? 'text-surface-500' : 'text-primary-600 dark:text-primary-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @switch($notification->type)
                            @case('overdue')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @break
                            @case('fine')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                @break
                            @case('hold_available')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                @break
                            @default
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        @endswitch
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-medium {{ $notification->is_read ? 'text-surface-700 dark:text-surface-300' : 'text-surface-900 dark:text-white' }}">
                            {{ $notification->title }}
                        </p>
                        <span class="text-xs text-surface-400 shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    @if($notification->body)
                        <p class="text-sm text-surface-500 dark:text-surface-400 mt-0.5">{{ $notification->body }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-2">
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" wire:navigate class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View Details</a>
                        @endif
                        @if(!$notification->is_read)
                            <button wire:click="markAsRead({{ $notification->id }})" class="text-xs text-surface-500 hover:text-surface-700 dark:hover:text-surface-300">Dismiss</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-surface-300 dark:text-surface-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-surface-500 dark:text-surface-400">No notifications yet</p>
                <p class="text-sm text-surface-400 dark:text-surface-500 mt-1">Notifications about due dates, holds, and fines will appear here.</p>
            </div>
        @endforelse
    </div>
</div>
