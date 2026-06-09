@section('title', 'Email Settings')
<div>
    <x-slot name="header">Email Settings</x-slot>
    <x-slot name="subtitle">Configure SMTP and email delivery options</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">From Name</label>
                        <input type="text" wire:model="settings.mail_from_name" class="input-field">
                        @error("settings.mail_from_name") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">From Address</label>
                        <input type="email" wire:model="settings.mail_from_address" class="input-field">
                        @error("settings.mail_from_address") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Mail Driver</label>
                        <select wire:model="settings.mail_driver" class="input-field">
                            <option value="smtp">SMTP</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="mailgun">Mailgun</option>
                            <option value="ses">Amazon SES</option>
                            <option value="postmark">Postmark</option>
                            <option value="log">Log (testing)</option>
                        </select>
                        @error("settings.mail_driver") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Mail Host</label>
                        <input type="text" wire:model="settings.mail_host" class="input-field" placeholder="smtp.example.com">
                        @error("settings.mail_host") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Mail Port</label>
                        <input type="text" wire:model="settings.mail_port" class="input-field" placeholder="587">
                        @error("settings.mail_port") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Encryption</label>
                        <select wire:model="settings.mail_encryption" class="input-field">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="null">None</option>
                        </select>
                        @error("settings.mail_encryption") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">SMTP Username</label>
                        <input type="text" wire:model="settings.mail_username" class="input-field" placeholder="SMTP username">
                        @error("settings.mail_username") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">SMTP Password</label>
                        <input type="password" wire:model="password" class="input-field" placeholder="{{ $hasPassword ? 'Leave empty to keep current password' : 'Enter SMTP password' }}">
                        @if($hasPassword)
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">A password is currently saved. Enter a new one to change it, or leave blank to keep it.</p>
                        @endif
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

    {{-- Test Email --}}
    <div class="card mt-6">
        <div class="card-header">
            <h3 class="font-semibold text-surface-900 dark:text-white">Test Email Delivery</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Send a test email to verify your configuration is working.</p>
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="label">Send Test To</label>
                    <input type="email" wire:model="testEmail" class="input-field w-full" placeholder="your@email.com">
                </div>
                <button wire:click="sendTestEmail" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove>
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Send Test
                    </span>
                    <span wire:loading>Sending...</span>
                </button>
            </div>

            @if($testResult)
                <div class="mt-4 p-3 rounded-xl {{ $testSuccess ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' }} text-sm">
                    {{ $testResult }}
                </div>
            @endif
        </div>
    </div>
</div>
