@section('title', 'Appearance')
<div>
    <x-slot name="header">Appearance Settings</x-slot>
    <x-slot name="subtitle">Customize the look and feel of your library system</x-slot>

    @if ($saved)
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm">
            Appearance settings saved successfully.
        </div>
    @endif

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Theme</label>
                        <select wire:model="settings.theme" class="input-field">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="auto">Auto (follow system)</option>
                        </select>
                        @error("settings.theme") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Primary Color</label>
                        <div class="flex gap-3 items-center">
                            <input type="color" wire:model="settings.primary_color" class="w-10 h-10 rounded-lg cursor-pointer border border-surface-200 dark:border-surface-600">
                            <input type="text" wire:model="settings.primary_color" class="input-field flex-1" placeholder="#1E4FA3">
                        </div>
                        @error("settings.primary_color") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Site Logo</label>
                        @if ($currentLogoUrl)
                            <div class="mb-3 flex items-center gap-3">
                                <img src="{{ $currentLogoUrl }}" alt="Current logo" class="h-12 w-auto rounded border border-surface-200 dark:border-surface-600 object-contain bg-white">
                                <button type="button" wire:click="removeLogo" wire:confirm="Remove this logo?" class="text-sm text-accent-600 hover:text-accent-700 underline">Remove</button>
                            </div>
                        @endif
                        <div x-data="{
                            cropping: false,
                            cropTarget: null,
                            initCropper() {},
                            fileSelected(event) {
                                const file = event.target.files[0];
                                if (!file) return;
                                this.cropTarget = 'siteLogo';
                                this.cropping = true;
                                this.$nextTick(() => {
                                    const img = this.$refs.cropImage;
                                    img.src = URL.createObjectURL(file);
                                    img.onload = () => {
                                        __initCropper(img, 1);
                                    };
                                });
                            },
                            async confirmCrop() {
                                if (!this.cropTarget) return;
                                const blob = await __getCroppedBlob('image/png');
                                if (!blob) return;
                                const croppedFile = new File([blob], 'logo.png', { type: 'image/png' });
                                await $wire.upload(this.cropTarget, croppedFile);
                                this.cropping = false;
                                __destroyCropper();
                                this.cropTarget = null;
                            },
                            cancelCrop() {
                                this.cropping = false;
                                __destroyCropper();
                                this.cropTarget = null;
                            }
                        }">
                            <input type="file" x-on:change="fileSelected" accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml"
                                   class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-300">
                            <div wire:loading wire:target="siteLogo" class="mt-2 text-sm text-primary-600">Uploading...</div>
                            @error("siteLogo") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror

                            <div x-show="cropping" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @keydown.escape.window="cancelCrop">
                                <div class="bg-white dark:bg-surface-800 rounded-2xl shadow-2xl max-w-3xl w-full mx-4 overflow-hidden" @click.outside="cancelCrop">
                                    <div class="p-4 border-b border-surface-200 dark:border-surface-700 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-surface-900 dark:text-white">Crop Logo</h3>
                                        <button type="button" @click="cancelCrop" class="p-1 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-4 bg-surface-100 dark:bg-surface-900/50">
                                        <img x-ref="cropImage" class="max-w-full max-h-[60vh] mx-auto">
                                    </div>
                                    <div class="p-4 border-t border-surface-200 dark:border-surface-700 flex justify-end gap-3">
                                        <button type="button" @click="cancelCrop" class="btn-secondary">Cancel</button>
                                        <button type="button" @click="confirmCrop" class="btn-primary">Apply Crop</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="label">Favicon</label>
                        @if ($currentFaviconUrl)
                            <div class="mb-3 flex items-center gap-3">
                                <img src="{{ $currentFaviconUrl }}" alt="Current favicon" class="h-8 w-8 rounded border border-surface-200 dark:border-surface-600 object-contain bg-white">
                                <button type="button" wire:click="removeFavicon" wire:confirm="Remove this favicon?" class="text-sm text-accent-600 hover:text-accent-700 underline">Remove</button>
                            </div>
                        @endif
                        <div x-data="{
                            cropping: false,
                            cropTarget: null,
                            fileSelected(event) {
                                const file = event.target.files[0];
                                if (!file) return;
                                this.cropTarget = 'favicon';
                                this.cropping = true;
                                this.$nextTick(() => {
                                    const img = this.$refs.cropImage;
                                    img.src = URL.createObjectURL(file);
                                    img.onload = () => {
                                        __initCropper(img, 1);
                                    };
                                });
                            },
                            async confirmCrop() {
                                if (!this.cropTarget) return;
                                const blob = await __getCroppedBlob('image/png');
                                if (!blob) return;
                                const mime = this.cropTarget === 'favicon' ? 'image/x-icon' : 'image/png';
                                const ext = this.cropTarget === 'favicon' ? 'ico' : 'png';
                                const croppedFile = new File([blob], 'favicon.' + ext, { type: blob.type });
                                await $wire.upload(this.cropTarget, croppedFile);
                                this.cropping = false;
                                __destroyCropper();
                                this.cropTarget = null;
                            },
                            cancelCrop() {
                                this.cropping = false;
                                __destroyCropper();
                                this.cropTarget = null;
                            }
                        }">
                            <input type="file" x-on:change="fileSelected" accept="image/x-icon,image/png,image/svg+xml"
                                   class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-300">
                            <div wire:loading wire:target="favicon" class="mt-2 text-sm text-primary-600">Uploading...</div>
                            @error("favicon") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror

                            <div x-show="cropping" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @keydown.escape.window="cancelCrop">
                                <div class="bg-white dark:bg-surface-800 rounded-2xl shadow-2xl max-w-3xl w-full mx-4 overflow-hidden" @click.outside="cancelCrop">
                                    <div class="p-4 border-b border-surface-200 dark:border-surface-700 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-surface-900 dark:text-white">Crop Favicon</h3>
                                        <button type="button" @click="cancelCrop" class="p-1 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-4 bg-surface-100 dark:bg-surface-900/50">
                                        <img x-ref="cropImage" class="max-w-full max-h-[60vh] mx-auto">
                                    </div>
                                    <div class="p-4 border-t border-surface-200 dark:border-surface-700 flex justify-end gap-3">
                                        <button type="button" @click="cancelCrop" class="btn-secondary">Cancel</button>
                                        <button type="button" @click="confirmCrop" class="btn-primary">Apply Crop</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="settings.sidebar_collapsed" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Collapsed sidebar by default</span>
                        </label>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="settings.show_analytics" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                            <span class="text-sm text-surface-700 dark:text-surface-300">Show analytics dashboard</span>
                        </label>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t-2 border-surface-200 dark:border-surface-700">
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-1">Document Branding</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">Configure how documents (reports, certificates) look when generated as PDF</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label">Document Logo</label>
                            @if ($currentDocumentLogoUrl)
                                <div class="mb-3 flex items-center gap-3">
                                    <img src="{{ $currentDocumentLogoUrl }}" alt="Document logo" class="h-14 w-auto rounded border border-surface-200 dark:border-surface-600 object-contain bg-white">
                                    <button type="button" wire:click="removeDocumentLogo" wire:confirm="Remove document logo?" class="text-sm text-accent-600 hover:text-accent-700 underline">Remove</button>
                                </div>
                            @endif
                            <input type="file" wire:model="documentLogo" accept="image/jpeg,image/png,image/jpg,image/webp"
                                   class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-300">
                            <div wire:loading wire:target="documentLogo" class="mt-1 text-sm text-primary-600">Uploading...</div>
                            @error("documentLogo") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label">Document Primary Color</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" wire:model="settings.document_primary_color" class="w-10 h-10 rounded-lg cursor-pointer border border-surface-200 dark:border-surface-600">
                                <input type="text" wire:model="settings.document_primary_color" class="input-field flex-1" placeholder="#1E4FA3">
                            </div>
                            @error("settings.document_primary_color") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label">Document Header Text</label>
                            <input type="text" wire:model="settings.document_header_text" class="input-field" placeholder="Official Library Document">
                            @error("settings.document_header_text") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label">Document Footer Text</label>
                            <input type="text" wire:model="settings.document_footer_text" class="input-field" placeholder="Official Library Document">
                            @error("settings.document_footer_text") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="label">Footer Disclaimer</label>
                            <textarea wire:model="settings.document_footer_disclaimer" class="input-field" rows="2" placeholder="This document is electronically generated..."></textarea>
                            @error("settings.document_footer_disclaimer") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3 pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settings.document_show_verification_stamp" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <span class="text-sm text-surface-700 dark:text-surface-300">Show verification stamp on documents</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settings.document_show_qr_code" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                                <span class="text-sm text-surface-700 dark:text-surface-300">Show QR code on documents</span>
                            </label>
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
