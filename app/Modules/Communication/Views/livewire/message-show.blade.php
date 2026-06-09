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

            <div class="p-4 border-t border-surface-200 dark:border-surface-700 flex items-center gap-3">
                <button wire:click="delete" wire:confirm="Delete this message?"
                    class="btn-danger btn-sm">
                    Delete Message
                </button>
                <a href="{{ route('communication.messages.index') }}" wire:navigate class="btn-secondary btn-sm">
                    Back to Messages
                </a>
            </div>
        </div>
    </div>
</div>
