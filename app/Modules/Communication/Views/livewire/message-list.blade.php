@section('title', 'Messages')
<div>
    <x-slot name="header">Messages</x-slot>
    <x-slot name="subtitle">Send and manage messages</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-2 flex-wrap">
            <button wire:click="$set('tab', 'inbox')"
                class="btn-{{ $tab === 'inbox' ? 'primary' : 'secondary' }} btn-sm">
                Inbox
                @if($unreadCount > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-white/20">{{ $unreadCount }}</span>
                @endif
            </button>
            <button wire:click="$set('tab', 'sent')"
                class="btn-{{ $tab === 'sent' ? 'primary' : 'secondary' }} btn-sm">
                Sent
            </button>
            <button wire:click="$set('tab', 'drafts')"
                class="btn-{{ $tab === 'drafts' ? 'primary' : 'secondary' }} btn-sm">
                Drafts
                @if(($draftCount ?? 0) > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-white/20">{{ $draftCount }}</span>
                @endif
            </button>
            @can('manage-scheduled-messages')
            <button wire:click="$set('tab', 'scheduled')"
                class="btn-{{ $tab === 'scheduled' ? 'primary' : 'secondary' }} btn-sm">
                Scheduled
                @if(($stats['total_scheduled'] ?? 0) > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-white/20">{{ $stats['total_scheduled'] }}</span>
                @endif
            </button>
            @endcan
        </div>
        <a href="{{ route('communication.messages.create') }}" wire:navigate class="btn-primary btn-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Message
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20">
            <p class="text-xs text-primary-600 dark:text-primary-400">Sent</p>
            <p class="text-lg font-bold text-primary-700 dark:text-primary-300">{{ $stats['total_sent'] ?? 0 }}</p>
        </div>
        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20">
            <p class="text-xs text-amber-600 dark:text-amber-400">Unread</p>
            <p class="text-lg font-bold text-amber-700 dark:text-amber-300">{{ $stats['total_unread'] ?? 0 }}</p>
        </div>
        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
            <p class="text-xs text-emerald-600 dark:text-emerald-400">This Month</p>
            <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ $stats['messages_this_month'] ?? 0 }}</p>
        </div>
        <div class="p-3 rounded-xl bg-accent-50 dark:bg-accent-900/20">
            <p class="text-xs text-accent-600 dark:text-accent-400">Scheduled</p>
            <p class="text-lg font-bold text-accent-700 dark:text-accent-300">{{ $stats['total_scheduled'] ?? 0 }}</p>
        </div>
        <div class="p-3 rounded-xl bg-amber-100 dark:bg-amber-900/20">
            <p class="text-xs text-amber-700 dark:text-amber-400">Drafts</p>
            <p class="text-lg font-bold text-amber-800 dark:text-amber-300">{{ $stats['total_drafts'] ?? 0 }}</p>
        </div>
    </div>

    <div class="card">
        <div class="p-4 border-b border-surface-200 dark:border-surface-700">
            <div class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search messages..."
                        class="input-field w-full">
                </div>
            </div>
        </div>

        <div class="divide-y divide-surface-200 dark:divide-surface-700">
            @forelse($messages as $item)
                @php
                    if (in_array($tab, ['drafts', 'sent', 'scheduled'])) {
                        $message = $item;
                    } else {
                        $message = $item->message;
                    }
                @endphp
                @if($message)
                @if($tab === 'scheduled')
                <div class="flex items-start gap-4 p-4 bg-accent-50/50 dark:bg-accent-900/10">
                    <div class="shrink-0 w-10 h-10 rounded-full bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center text-accent-600 dark:text-accent-400 text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-surface-900 dark:text-white truncate">{{ $message->subject }}</p>
                            <span class="text-xs text-surface-400 shrink-0 ml-2">{{ $message->scheduled_at?->format('M d, Y g:i A') }}</span>
                        </div>
                        <p class="text-sm text-accent-600 dark:text-accent-400 font-medium">Scheduled</p>
                        <p class="text-sm text-surface-400 truncate">{{ Str::limit(strip_tags($message->body), 100) }}</p>
                    </div>
                    @can('manage-scheduled-messages')
                    <button wire:click="cancelScheduled({{ $message->id }})" wire:confirm="Cancel this scheduled message?"
                        class="btn-danger btn-sm shrink-0">
                        Cancel
                    </button>
                    @endcan
                </div>
                @elseif($tab === 'drafts')
                <a href="{{ route('communication.messages.create', ['id' => $message->id]) }}" wire:navigate
                    class="flex items-start gap-4 p-4 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors bg-amber-50/50 dark:bg-amber-900/10">
                    <div class="shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-surface-900 dark:text-white truncate">
                                {{ $message->subject ?: '(No subject)' }}
                            </p>
                            <span class="text-xs text-surface-400 shrink-0 ml-2">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-amber-600 dark:text-amber-400 font-medium">Draft</p>
                        <p class="text-sm text-surface-400 truncate">{{ Str::limit(strip_tags($message->body), 100) }}</p>
                    </div>
                </a>
                @else
                <a href="{{ route('communication.messages.show', $message->id) }}" wire:navigate
                    class="flex items-start gap-4 p-4 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors {{ ($tab === 'inbox' && !$item->is_read) ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                    <div class="shrink-0 w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-sm font-bold">
                        {{ strtoupper(substr($message->sender?->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-surface-900 dark:text-white truncate {{ ($tab === 'inbox' && !$item->is_read) ? 'font-semibold' : '' }}">
                                {{ $message->subject }}
                            </p>
                            <span class="text-xs text-surface-400 shrink-0 ml-2">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-surface-500 dark:text-surface-400">
                            {{ $tab === 'sent' ? 'To: ' . $message->recipients->pluck('recipient.name')->filter()->implode(', ') : $message->sender?->name }}
                        </p>
                        <p class="text-sm text-surface-400 truncate">{{ Str::limit(strip_tags($message->body), 100) }}</p>
                    </div>
                    @if($message->priority === 'high')
                        <span class="shrink-0 px-2 py-0.5 text-xs font-medium rounded-full bg-accent-100 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">High</span>
                    @endif
                </a>
                @endif
                @endif
            @empty
                <div class="p-8 text-center text-surface-400">
                    <p>No messages found.</p>
                    <a href="{{ route('communication.messages.create') }}" wire:navigate class="btn-primary btn-sm mt-3 inline-flex">Send your first message</a>
                </div>
            @endforelse
        </div>

        @if($messages->hasPages())
            <div class="p-4 border-t border-surface-200 dark:border-surface-700">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
