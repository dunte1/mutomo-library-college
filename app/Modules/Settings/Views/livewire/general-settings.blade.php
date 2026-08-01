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

                    <div>
                        <label class="label">Library Website</label>
                        <input type="url" wire:model="settings.library_website" class="input-field" placeholder="https://www.ollmchs.ac.ke">
                        @error("settings.library_website") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Library Motto / Tagline</label>
                        <input type="text" wire:model="settings.library_motto" class="input-field" placeholder="e.g. Learn • Discover • Succeed">
                        @error("settings.library_motto") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
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

                <div class="border-t border-surface-200 dark:border-surface-700 pt-6 mt-6">
                    <h3 class="text-base font-semibold text-surface-900 dark:text-white mb-1">Library Card Branding</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Customize the colors and logo used on library cards.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="label">Primary Color (Header Gradient Start)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="settings.card_primary_color" class="h-10 w-10 rounded border border-surface-300 dark:border-surface-600 cursor-pointer">
                                <input type="text" wire:model="settings.card_primary_color" class="input-field flex-1" placeholder="#1a365d">
                            </div>
                        </div>
                        <div>
                            <label class="label">Secondary Color (Header Gradient Mid)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="settings.card_secondary_color" class="h-10 w-10 rounded border border-surface-300 dark:border-surface-600 cursor-pointer">
                                <input type="text" wire:model="settings.card_secondary_color" class="input-field flex-1" placeholder="#153168">
                            </div>
                        </div>
                        <div>
                            <label class="label">Tertiary Color (Header Gradient End)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="settings.card_tertiary_color" class="h-10 w-10 rounded border border-surface-300 dark:border-surface-600 cursor-pointer">
                                <input type="text" wire:model="settings.card_tertiary_color" class="input-field flex-1" placeholder="#0f2453">
                            </div>
                        </div>
                        <div>
                            <label class="label">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="settings.card_text_color" class="h-10 w-10 rounded border border-surface-300 dark:border-surface-600 cursor-pointer">
                                <input type="text" wire:model="settings.card_text_color" class="input-field flex-1" placeholder="#ffffff">
                            </div>
                        </div>
                        <div>
                            <label class="label">Accent Color (Labels &amp; Subtitles)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="settings.card_accent_color" class="h-10 w-10 rounded border border-surface-300 dark:border-surface-600 cursor-pointer">
                                <input type="text" wire:model="settings.card_accent_color" class="input-field flex-1" placeholder="#93c5fd">
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="label">Principal Name</label>
                            <input type="text" wire:model="settings.principal_name" class="input-field" placeholder="e.g. Dr. Jane Wanjiku">
                            <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Shown next to the principal's signature at the bottom-right of library cards.</p>
                            @error("settings.principal_name") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="label">Card Logo</label>
                        <p class="text-xs text-surface-500 dark:text-surface-400 mb-2">Upload a logo to display on library cards. Recommended size: 200x200px, PNG or SVG.</p>
                        <div class="flex items-start gap-4">
                            <div class="shrink-0">
                                @if($currentCardLogo)
                                    <img src="{{ Storage::url($currentCardLogo) }}" alt="Card Logo" class="w-20 h-20 object-contain rounded-lg border border-surface-200 dark:border-surface-600 bg-white p-1">
                                @else
                                    <div class="w-20 h-20 rounded-lg border-2 border-dashed border-surface-300 dark:border-surface-600 flex items-center justify-center bg-surface-50 dark:bg-surface-800">
                                        <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <label class="btn-outline btn-sm cursor-pointer">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        Choose Logo
                                        <input type="file" wire:model="cardLogo" accept="image/*" class="hidden">
                                    </label>
                                    @if($cardLogo)
                                        <button type="button" wire:click="saveCardLogo" class="btn-primary btn-sm">
                                            Upload
                                        </button>
                                    @endif
                                    @if($currentCardLogo)
                                        <button type="button" wire:click="removeCardLogo" wire:confirm="Remove the card logo?"
                                            class="btn-sm btn-danger">
                                            Remove
                                        </button>
                                    @endif
                                </div>
                                @error("cardLogo") <p class="text-sm text-accent-600">{{ $message }}</p> @enderror
                                @if($cardLogo)
                                    <p class="text-xs text-surface-500">Selected: {{ $cardLogo->getClientOriginalName() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="label">Principal Signature</label>
                        <p class="text-xs text-surface-500 dark:text-surface-400 mb-2">Upload the principal's signature to display at the bottom-right of library cards. Recommended: a wide transparent PNG.</p>
                        <div class="flex items-start gap-4">
                            <div class="shrink-0">
                                @if($currentPrincipalSignature)
                                    <img src="{{ Storage::url($currentPrincipalSignature) }}" alt="Principal Signature" class="h-16 w-40 object-contain rounded-lg border border-surface-200 dark:border-surface-600 bg-white p-1">
                                @else
                                    <div class="h-16 w-40 rounded-lg border-2 border-dashed border-surface-300 dark:border-surface-600 flex items-center justify-center bg-surface-50 dark:bg-surface-800">
                                        <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 space-y-2">
                                <div x-data="{
                                    cropping: false,
                                    fileSelected(event) {
                                        const file = event.target.files[0];
                                        if (!file) return;
                                        this.cropping = true;
                                        this.$nextTick(async () => {
                                            const img = this.$refs.sigCropImage;
                                            img.src = await __readFileAsDataURL(file);
                                            img.onload = () => { __initCropper(img, 4 / 1); };
                                        });
                                    },
                                    async confirmCrop() {
                                        const blob = await __getCroppedBlob('image/png');
                                        if (!blob) return;
                                        const croppedFile = new File([blob], 'signature.png', { type: 'image/png' });
                                        await $wire.upload('principalSignature', croppedFile);
                                        this.cropping = false;
                                        __destroyCropper();
                                    },
                                    cancelCrop() {
                                        this.cropping = false;
                                        __destroyCropper();
                                    }
                                }">
                                    <div class="flex items-center gap-2">
                                        <label class="btn-outline btn-sm cursor-pointer">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            Choose Signature
                                            <input type="file" x-on:change="fileSelected" accept="image/*" class="hidden">
                                        </label>
                                        <div wire:loading wire:target="principalSignature" class="text-sm text-primary-600">Uploading...</div>
                                    </div>
                                    @if($currentPrincipalSignature)
                                        <button type="button" wire:click="removePrincipalSignature" wire:confirm="Remove the principal signature?"
                                            class="btn-sm btn-danger">
                                            Remove
                                        </button>
                                    @endif
                                    @error("principalSignature") <p class="text-sm text-accent-600">{{ $message }}</p> @enderror

                                    <div x-show="cropping" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @keydown.escape.window="cancelCrop">
                                        <div class="bg-white dark:bg-surface-800 rounded-2xl shadow-2xl max-w-3xl w-full mx-4 overflow-hidden" @click.outside="cancelCrop">
                                            <div class="p-4 border-b border-surface-200 dark:border-surface-700 flex items-center justify-between">
                                                <h3 class="text-lg font-semibold text-surface-900 dark:text-white">Crop Signature</h3>
                                                <button type="button" @click="cancelCrop" class="p-1 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="p-4 bg-surface-100 dark:bg-surface-900/50">
                                                <img x-ref="sigCropImage" class="max-w-full max-h-[60vh] mx-auto">
                                            </div>
                                            <div class="p-4 border-t border-surface-200 dark:border-surface-700 flex justify-end gap-3">
                                                <button type="button" @click="cancelCrop" class="btn-secondary">Cancel</button>
                                                <button type="button" @click="confirmCrop" class="btn-primary">Apply Crop</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
