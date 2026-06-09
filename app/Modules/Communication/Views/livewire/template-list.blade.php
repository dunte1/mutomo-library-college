@section('title', 'Message Templates')
<div>
    <x-slot name="header">Message Templates</x-slot>
    <x-slot name="subtitle">Create and manage reusable message templates</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-2">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search templates..."
                class="input-field w-64">
            <select wire:model.live="categoryFilter" class="input-field">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('communication.templates.create') }}" wire:navigate class="btn-primary btn-sm">
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Template
        </a>
    </div>

    <div class="card">
        <div class="divide-y divide-surface-200 dark:divide-surface-700">
            @forelse($templates as $template)
            <div class="p-4 flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-medium text-surface-900 dark:text-white">{{ $template->name }}</h3>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-surface-100 dark:bg-surface-700 text-surface-500">{{ $template->category ?? 'Uncategorized' }}</span>
                        @if(!$template->is_active)
                            <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Inactive</span>
                        @endif
                    </div>
                    <p class="text-sm text-surface-500 mt-0.5">{{ Str::limit($template->subject, 80) }}</p>
                    <p class="text-xs text-surface-400 mt-1">Created by {{ $template->creator?->name }} &middot; {{ $template->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button wire:click="toggleActive({{ $template->id }})" class="btn-secondary btn-sm text-xs">
                        {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <a href="{{ route('communication.templates.edit', $template->id) }}" wire:navigate class="btn-secondary btn-sm text-xs">Edit</a>
                    <button wire:click="delete({{ $template->id }})" wire:confirm="Delete this template?"
                        class="btn-danger btn-sm text-xs">Delete</button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-surface-400">
                <p>No templates yet.</p>
                <a href="{{ route('communication.templates.create') }}" wire:navigate class="btn-primary btn-sm mt-3 inline-flex">Create your first template</a>
            </div>
            @endforelse
        </div>

        @if($templates->hasPages())
            <div class="p-4 border-t border-surface-200 dark:border-surface-700">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>
