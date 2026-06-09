@section('title', 'Localization')
<div>
    <x-slot name="header">Localization Settings</x-slot>
    <x-slot name="subtitle">Set language, timezone and regional preferences</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Default Language</label>
                        <select wire:model="settings.default_language" class="input-field">
                            <option value="en">English</option>
                            <option value="sw">Swahili</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="es">Spanish</option>
                        </select>
                        @error("settings.default_language") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Timezone</label>
                        <select wire:model="settings.default_timezone" class="input-field">
                            <option value="Africa/Nairobi">Africa/Nairobi (UTC+3)</option>
                            <option value="Africa/Dar_es_Salaam">Africa/Dar es Salaam (UTC+3)</option>
                            <option value="Africa/Kampala">Africa/Kampala (UTC+3)</option>
                            <option value="Africa/Lagos">Africa/Lagos (UTC+1)</option>
                            <option value="Africa/Cairo">Africa/Cairo (UTC+2)</option>
                            <option value="America/New_York">America/New York (UTC-5)</option>
                            <option value="America/Chicago">America/Chicago (UTC-6)</option>
                            <option value="America/Denver">America/Denver (UTC-7)</option>
                            <option value="America/Los_Angeles">America/Los Angeles (UTC-8)</option>
                            <option value="Europe/London">Europe/London (UTC+0)</option>
                            <option value="Asia/Dubai">Asia/Dubai (UTC+4)</option>
                            <option value="Asia/Singapore">Asia/Singapore (UTC+8)</option>
                        </select>
                        @error("settings.default_timezone") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Date Format</label>
                        <input type="text" wire:model="settings.date_format" class="input-field" placeholder="d M Y">
                        <p class="text-xs text-surface-400 mt-1">PHP date format (e.g., d M Y, Y-m-d, m/d/Y)</p>
                        @error("settings.date_format") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Time Format</label>
                        <input type="text" wire:model="settings.time_format" class="input-field" placeholder="H:i">
                        <p class="text-xs text-surface-400 mt-1">PHP time format (e.g., H:i, h:i A)</p>
                        @error("settings.time_format") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Currency</label>
                        <select wire:model="settings.currency" class="input-field">
                            <option value="KES">KES - Kenyan Shilling</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="TZS">TZS - Tanzanian Shilling</option>
                            <option value="UGX">UGX - Ugandan Shilling</option>
                        </select>
                        @error("settings.currency") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">First Day of Week</label>
                        <select wire:model="settings.first_day_of_week" class="input-field">
                            <option value="monday">Monday</option>
                            <option value="sunday">Sunday</option>
                            <option value="saturday">Saturday</option>
                        </select>
                        @error("settings.first_day_of_week") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
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
