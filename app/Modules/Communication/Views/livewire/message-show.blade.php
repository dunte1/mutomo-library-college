@section('title', $message->subject)
<div>
    <x-slot name="header">{{ $message->subject }}</x-slot>
    <x-slot name="subtitle">Message details</x-slot>

    <div class="max-w-3xl">
        <div class="card">
            <div class="p-6 border-b border-surface-200 dark:border-surface-700">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-lg font-bold">
                        {{ strtoupper(substr($message->sender?->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-surface-900 dark:text-white">{{ $message->sender?->name }}</p>
                                <p class="text-sm text-surface-400">{{ $message->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($message->priority === 'high')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-accent-100 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">High Priority</span>
                                @endif
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                                    {{ ucfirst($message->type) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($message->recipients as $recipient)
                                <span class="text-xs text-surface-500 dark:text-surface-400">
                                    {{ $recipient->recipient?->name ?? 'All Users' }}{{ !$loop->last ? ',' : '' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="prose dark:prose-invert max-w-none">
                    {!! nl2br(e($message->body)) !!}
                </div>
            </div>

            @if($message->replies->isNotEmpty())
            <div class="p-6 border-t border-surface-200 dark:border-surface-700">
                <h3 class="font-medium text-surface-900 dark:text-white mb-4">Replies ({{ $message->replies->count() }})</h3>
                <div class="space-y-4">
                    @foreach($message->replies as $reply)
                    <div class="flex items-start gap-3 p-4 rounded-lg bg-surface-50 dark:bg-surface-800">
                        <div class="shrink-0 w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-sm font-bold">
                            {{ strtoupper(substr($reply->sender?->name ?? '?', 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-surface-900 dark:text-white">{{ $reply->sender?->name }}</p>
                                <span class="text-xs text-surface-400">{{ $reply->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <div class="mt-2 text-sm text-surface-700 dark:text-surface-300 prose-sm">
                                {!! nl2br(e($reply->body)) !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($message->attachments->isNotEmpty())
            <div class="p-6 border-t border-surface-200 dark:border-surface-700">
                <h3 class="font-medium text-surface-900 dark:text-white mb-3">Attachments ({{ $message->attachments->count() }})</h3>
                <div class="space-y-2">
                    @foreach($message->attachments as $attachment)
                    <a href="{{ $attachment->url }}" target="_blank"
                        class="flex items-center gap-3 p-3 rounded-lg bg-surface-50 dark:bg-surface-800 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                        <svg class="w-5 h-5 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span class="flex-1 text-sm text-surface-700 dark:text-surface-300">{{ $attachment->file_name }}</span>
                        <span class="text-xs text-surface-400">{{ $attachment->size_for_humans }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($message->recipients->isNotEmpty())
            <div class="p-6 border-t border-surface-200 dark:border-surface-700">
                <h3 class="font-medium text-surface-900 dark:text-white mb-3">Delivery Status</h3>
                <div class="space-y-2">
                    @foreach($message->recipients as $recipient)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-surface-50 dark:bg-surface-800">
                        <span class="text-sm text-surface-700 dark:text-surface-300">{{ $recipient->recipient?->name ?? 'All Users' }}</span>
                        <div class="flex items-center gap-2">
                            @if($recipient->is_read)
                                <span class="text-xs text-emerald-600 dark:text-emerald-400">Read {{ $recipient->read_at?->diffForHumans() }}</span>
                            @elseif($recipient->delivery_status === 'delivered')
                                <span class="text-xs text-primary-600 dark:text-primary-400">Delivered</span>
                            @else
                                <span class="text-xs text-amber-600 dark:text-amber-400">Pending</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="p-4 border-t border-surface-200 dark:border-surface-700">
                <div class="flex flex-wrap items-center gap-2">
                    @can('reply-messages')
                    <button wire:click="$set('showReplyAll', false)" wire:loading.attr="disabled"
                        class="btn-secondary btn-sm">
                        Reply
                    </button>
                    @endcan
                    @can('reply-all-messages')
                    <button wire:click="$toggle('showReplyAll')" wire:loading.attr="disabled"
                        class="btn-secondary btn-sm">
                        Reply All
                    </button>
                    @endcan
                    @can('forward-messages')
                    <button wire:click="toggleForward" wire:loading.attr="disabled"
                        class="btn-secondary btn-sm">
                        Forward
                    </button>
                    @endcan
                    <button wire:click="delete" wire:confirm="Delete this message?"
                        class="btn-danger btn-sm ml-auto">
                        Delete
                    </button>
                    <a href="{{ route('communication.messages.index') }}" wire:navigate class="btn-secondary btn-sm">
                        Back
                    </a>
                </div>
            </div>
        </div>

        @can('reply-messages')
        <div class="card mt-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-surface-900 dark:text-white">
                        {{ $showReplyAll ? 'Reply All' : 'Reply' }}
                    </h3>
                    @can('reply-all-messages')
                    <button type="button" wire:click="$toggle('showReplyAll')"
                        class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        {{ $showReplyAll ? 'Reply to sender only' : 'Reply to all' }}
                    </button>
                    @endcan
                </div>
                <textarea wire:model="replyBody" rows="4"
                    class="input w-full resize-none"
                    placeholder="Write your reply..."></textarea>
                @error('replyBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-3 flex justify-end">
                    <button wire:click="{{ $showReplyAll ? 'sendReplyAll' : 'sendReply' }}" class="btn-primary btn-sm">
                        Send {{ $showReplyAll ? 'Reply All' : 'Reply' }}
                    </button>
                </div>
            </div>
        </div>
        @endcan

        @can('forward-messages')
        @if($showForward)
        <div class="card mt-6">
            <div class="p-6">
                <h3 class="font-medium text-surface-900 dark:text-white mb-4">Forward Message</h3>
                <form wire:submit="sendForward">
                    <div class="relative" x-data="{ open: false }">
                        <input wire:model.live.debounce.300ms="forwardRecipientSearch"
                            @focus="open = true" @click.away="setTimeout(() => open = false, 200)"
                            type="text" placeholder="Search recipients by name or email..."
                            class="input w-full text-sm">
                        <div x-show="open" x-cloak
                            class="absolute z-10 mt-1 w-full bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                            @forelse($forwardUsers as $user)
                            <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-50 dark:hover:bg-surface-700 cursor-pointer border-b border-surface-100 dark:border-surface-700/50 last:border-0">
                                <input type="checkbox" wire:model="selectedForwardRecipients" value="{{ $user->id }}"
                                    class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 shrink-0">
                                <span class="text-sm text-surface-800 dark:text-surface-200">{{ $user->name }} ({{ $user->email }})</span>
                            </label>
                            @empty
                            <div class="px-4 py-4 text-center text-sm text-surface-400">No users found.</div>
                            @endforelse
                        </div>
                    </div>
                    @error('selectedForwardRecipients') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if(count($selectedForwardRecipients) > 0)
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        @foreach($selectedForwardRecipients as $rid)
                        @php $fu = collect($forwardUsers)->firstWhere('id', $rid); @endphp
                        @if($fu)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                            {{ $fu->name }}
                            <button type="button" wire:click="removeForwardRecipient({{ $rid }})" class="ml-0.5 hover:text-accent-600">&times;</button>
                        </span>
                        @endif
                        @endforeach
                    </div>
                    @endif
                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" wire:click="toggleForward" class="btn-secondary btn-sm">Cancel</button>
                        <button type="submit" class="btn-primary btn-sm">Forward Message</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
