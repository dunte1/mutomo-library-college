@section('title', 'Digital Library Settings')
<div>
    <x-slot name="header">Digital Library Settings</x-slot>
    <x-slot name="subtitle">Manage upload limits, file types and access policies</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Max Upload Size (KB)</label>
                        <input type="number" wire:model="settings.max_upload_size" class="input-field" min="1024" max="1048576">
                        @error("settings.max_upload_size") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Max Assets Per User</label>
                        <input type="number" wire:model="settings.max_assets_per_user" class="input-field" min="1" max="10000">
                        @error("settings.max_assets_per_user") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Allowed File Types</label>
                        <input type="text" wire:model="settings.allowed_file_types" class="input-field" placeholder="pdf,doc,docx,ppt,pptx,mp4,mp3,epub">
                        <p class="text-xs text-surface-400 mt-1">Comma-separated list of allowed file extensions</p>
                        @error("settings.allowed_file_types") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="settings.auto_approve_uploads" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Auto-approve uploads</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
