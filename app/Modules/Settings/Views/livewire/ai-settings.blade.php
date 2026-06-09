@section('title', 'AI Settings')
<div>
    <x-slot name="header">AI / ML Settings</x-slot>
    <x-slot name="subtitle">Configure AI-powered recommendations and machine learning features</x-slot>

    <div class="card">
        <div class="card-body space-y-6">
            <form wire:submit="save">
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-surface-900 dark:text-white mb-3">General</h3>
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="settings.ai_enabled" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 dark:bg-surface-800 dark:border-surface-600">
                            <div>
                                <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Enable AI Features</span>
                                <p class="text-xs text-surface-400">Allow the system to use AI for book recommendations and predictive analytics</p>
                            </div>
                        </label>
                    </div>

                    <div class="border-t border-surface-200 dark:border-surface-700 pt-4">
                        <h3 class="font-semibold text-surface-900 dark:text-white mb-3">API Configuration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="label">API Endpoint</label>
                                <input type="url" wire:model="settings.api_endpoint" class="input-field" placeholder="https://api.example.com/v1">
                                @error("settings.api_endpoint") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">API Key</label>
                                <input type="password" wire:model="api_key" class="input-field" placeholder="Enter your API key">
                                @error("api_key") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                                <p class="text-xs text-surface-400 mt-1">Leave blank to keep the existing key</p>
                            </div>
                            <div>
                                <label class="label">Model Name</label>
                                <input type="text" wire:model="settings.model_name" class="input-field" placeholder="e.g. gpt-4, llama-3">
                                @error("settings.model_name") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-surface-200 dark:border-surface-700 pt-4">
                        <h3 class="font-semibold text-surface-900 dark:text-white mb-3">Recommendation Engine</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="label">Max Recommendations</label>
                                <input type="number" wire:model="settings.max_recommendations" class="input-field" min="1" max="100">
                                @error("settings.max_recommendations") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Confidence Threshold</label>
                                <input type="number" wire:model="settings.confidence_threshold" class="input-field" step="0.05" min="0" max="1">
                                @error("settings.confidence_threshold") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                                <p class="text-xs text-surface-400 mt-1">Minimum confidence score (0.0 - 1.0) for recommendations</p>
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
