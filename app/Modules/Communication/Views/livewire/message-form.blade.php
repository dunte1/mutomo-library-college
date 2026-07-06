@section('title', $messageId ? 'Edit Draft' : 'New Message')
<div>
    <x-slot name="header">{{ $messageId ? 'Edit Draft' : 'New Message' }}</x-slot>
    <x-slot name="subtitle">{{ $messageId ? 'Continue editing your draft' : 'Compose and send a message' }}</x-slot>

    <form wire:submit="save" class="max-w-3xl space-y-6">
        <div class="card p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label">Message Type</label>
                    <select wire:model.live="type" class="input-field w-full">
                        <option value="direct">Direct Message</option>
                        <option value="group">Group Message</option>
                        <option value="broadcast">Broadcast</option>
                        <option value="department">Department Message</option>
                        <option value="program">Program Message</option>
                    </select>
                    @error('type') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Priority</label>
                    <select wire:model="priority" class="input-field w-full">
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            @if(in_array($type, ['direct', 'group']))
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="label mb-0">Recipients</label>
                    @if(count($selectedRecipients) > 0)
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                        {{ count($selectedRecipients) }} selected
                    </span>
                    @endif
                </div>

                {{-- Bulk role selection --}}
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach($roles as $role)
                    <button type="button" wire:click="addByRole('{{ $role->name }}')"
                        class="text-xs px-2 py-1 rounded-md border border-surface-200 dark:border-surface-700 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:border-primary-300 dark:hover:border-primary-700 text-surface-600 dark:text-surface-400 transition-colors"
                        title="Add all {{ $role->name }}s">
                        +{{ ucfirst($role->name) }}
                    </button>
                    @endforeach
                </div>

                {{-- Search + dropdown list --}}
                <div class="relative" x-data="{ open: false }">
                    <div class="flex gap-2">
                        <input wire:model.live.debounce.300ms="recipientSearch" @focus="open = true" @click.away="setTimeout(() => open = false, 200)"
                            type="text" placeholder="Search by name, email, or admission #..." class="input-field w-full text-sm">
                        <button type="button" wire:click="selectAll" class="btn-secondary btn-xs whitespace-nowrap"
                            title="Select all visible users">Select All</button>
                        @if(count($selectedRecipients) > 0)
                        <button type="button" wire:click="deselectAll" class="btn-outline btn-xs whitespace-nowrap text-accent-600 border-accent-200 hover:bg-accent-50"
                            title="Clear all selected recipients">Clear</button>
                        @endif
                    </div>

                    <div x-show="open" x-cloak class="absolute z-10 mt-1 w-full bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                        @forelse($users as $user)
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-50 dark:hover:bg-surface-700 cursor-pointer border-b border-surface-100 dark:border-surface-700/50 last:border-0 transition-colors"
                            wire:key="user-{{ $user->id }}">
                            <input type="checkbox" wire:model="selectedRecipients" value="{{ $user->id }}"
                                class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 shrink-0">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-surface-800 dark:text-surface-200 truncate">{{ $user->name }}</div>
                                <div class="text-xs text-surface-400 truncate">{{ $user->email }}@if($user->department) &middot; {{ $user->department->name }}@endif</div>
                            </div>
                        </label>
                        @empty
                        <div class="px-4 py-6 text-center text-sm text-surface-400">
                            No users match your search.
                        </div>
                        @endforelse
                        @if($users->count() > 0)
                        <div class="px-4 py-2 text-xs text-surface-400 border-t border-surface-100 dark:border-surface-700/50 text-center">
                            {{ $users->count() }} user(s) found
                        </div>
                        @endif
                    </div>
                </div>

                @error('selectedRecipients') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror

                {{-- Selected recipients chips --}}
                @if(count($selectedRecipients) > 0)
                <div class="flex flex-wrap gap-1.5 mt-3 max-h-32 overflow-y-auto">
                    @foreach($selectedRecipients as $rid)
                        @php $su = $selectedUsers[$rid] ?? null; @endphp
                        @if($su)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="max-w-[150px] truncate">{{ $su->name }}</span>
                            @if($su->department)
                            <span class="text-primary-400 dark:text-primary-500">({{ $su->department->name }})</span>
                            @endif
                            <button type="button" wire:click="removeRecipient({{ $rid }})" class="ml-0.5 hover:text-accent-600 transition-colors shrink-0" title="Remove">&times;</button>
                        </span>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            @if($type === 'department')
            <div>
                <label class="label">Department</label>
                <select wire:model="department_id" class="input-field w-full">
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>
            @endif

            @if($type === 'program')
            <div>
                <label class="label">Program</label>
                <select wire:model="program_id" class="input-field w-full">
                    <option value="">Select Program</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                    @endforeach
                </select>
                @error('program_id') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>
            @endif

            @if($templates->isNotEmpty())
            <div>
                <label class="label">Use Template (optional)</label>
                <select wire:model.live="template_id" class="input-field w-full">
                    <option value="">-- Select Template --</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->category ?? 'Uncategorized' }})</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="label">Subject</label>
                <input wire:model="subject" type="text" class="input-field w-full" placeholder="Message subject...">
                @error('subject') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Message</label>
                <textarea wire:model="body" rows="6" class="input-field w-full" placeholder="Write your message..."></textarea>
                @error('body') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Attachments (max 5, 10MB each)</label>
                <input type="file" wire:model="attachments" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt"
                    class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-300">
                <div wire:loading wire:target="attachments" class="mt-1 text-sm text-primary-600">Uploading...</div>
                @error('attachments.*') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                @if(count($attachments) > 0)
                    <div class="mt-2 space-y-1">
                    @foreach($attachments as $idx => $attachment)
                        <div class="flex items-center justify-between p-2 bg-surface-50 dark:bg-surface-800 rounded-lg">
                            <span class="text-sm truncate">{{ $attachment->getClientOriginalName() }}</span>
                            <button type="button" wire:click="removeAttachment({{ $idx }})" class="text-accent-600 hover:text-accent-700 text-sm">&times;</button>
                        </div>
                    @endforeach
                    </div>
                @endif
            </div>

            <div>
                <label class="label">
                    <input type="checkbox" wire:model="scheduled_at" value="{{ now()->addHour()->format('Y-m-d H:i') }}" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 mr-2">
                    Schedule for later delivery
                </label>
                @if($scheduled_at)
                <input wire:model="scheduled_at" type="datetime-local" class="input-field w-full mt-2"
                    min="{{ now()->format('Y-m-d H:i') }}">
                @endif
                @error('scheduled_at') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    {{ $scheduled_at ? 'Schedule Message' : 'Send Message' }}
                </span>
                <span wire:loading>Sending...</span>
            </button>
            <button type="button" wire:click="saveDraft" class="btn-secondary" wire:loading.attr="disabled">
                <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Save Draft
            </button>
            <a href="{{ route('communication.messages.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
