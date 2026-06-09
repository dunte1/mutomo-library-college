@section('title', 'General Settings')
<div>
    <x-header title="General Settings" subtitle="Configure your library's basic information">
        <x-slot:actions>
            <a href="{{ route('settings.index') }}" wire:navigate class="btn-outline btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                Back to Settings
            </a>
        </x-slot:actions>
    </x-header>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Site Name</label>
                        <input type="text" wire:model="settings.site_name" class="input-field">
                        @error("settings.site_name") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Library Email</label>
                        <input type="email" wire:model="settings.library_email" class="input-field">
                        @error("settings.library_email") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Library Phone</label>
                        <input type="text" wire:model="settings.library_phone" class="input-field">
                        @error("settings.library_phone") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Opening Hours</label>
                        <input type="text" wire:model="settings.opening_hours" class="input-field" placeholder="e.g. Mon-Fri: 8:00 AM - 5:00 PM">
                        @error("settings.opening_hours") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Site Description</label>
                        <textarea wire:model="settings.site_description" class="input-field" rows="3"></textarea>
                        @error("settings.site_description") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Library Address</label>
                        <textarea wire:model="settings.library_address" class="input-field" rows="2"></textarea>
                        @error("settings.library_address") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-surface-200 dark:border-surface-700 pt-6 mt-6">
                    <h3 class="text-base font-semibold text-surface-900 dark:text-white mb-1">Footer Content</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Customize the footer displayed on the landing page.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label">Copyright Text</label>
                            <input type="text" wire:model="settings.footer_copyright" class="input-field" placeholder="e.g. All rights reserved.">
                            @error("settings.footer_copyright") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Facebook URL</label>
                            <input type="url" wire:model="settings.footer_facebook_url" class="input-field" placeholder="https://facebook.com/...">
                            @error("settings.footer_facebook_url") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Twitter / X URL</label>
                            <input type="url" wire:model="settings.footer_twitter_url" class="input-field" placeholder="https://twitter.com/...">
                            @error("settings.footer_twitter_url") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>
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
