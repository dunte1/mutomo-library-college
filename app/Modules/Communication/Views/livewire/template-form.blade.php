@section('title', $templateId ? 'Edit Template' : 'New Template')
<div>
    <x-slot name="header">{{ $templateId ? 'Edit Template' : 'New Template' }}</x-slot>
    <x-slot name="subtitle">{{ $templateId ? 'Update message template' : 'Create a reusable message template' }}</x-slot>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label">Template Name</label>
                    <input wire:model="name" type="text" class="input-field w-full" placeholder="e.g. Overdue Notice">
                    @error('name') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Category</label>
                    <input wire:model="category" type="text" class="input-field w-full" placeholder="e.g. Reminders">
                    @error('category') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="label">Subject Template</label>
                <input wire:model="subject" type="text" class="input-field w-full" placeholder="e.g. Book @{{title}} is overdue">                            <p class="text-xs text-surface-400 mt-1">Use <code>@{{variable}}</code> placeholders for dynamic content</p>
                            @error('subject') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Body Template</label>
                <textarea wire:model="body" rows="8" class="input-field w-full" placeholder="Write your template body with @{{variable}} placeholders..."></textarea>
                @error('body') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="isActive" id="isActive"
                    class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                <label for="isActive" class="text-sm text-surface-700 dark:text-surface-300">Active</label>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $templateId ? 'Update Template' : 'Create Template' }}</span>
                <span wire:loading>Saving...</span>
            </button>
            <a href="{{ route('communication.templates.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
