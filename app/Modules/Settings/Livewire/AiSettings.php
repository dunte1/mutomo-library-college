<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;

class AiSettings extends Component
{
    public array $settings = [];

    public string $api_key = '';

    protected function rules(): array
    {
        return [
            'settings.ai_enabled' => ['boolean'],
            'settings.api_endpoint' => ['nullable', 'string', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'settings.model_name' => ['nullable', 'string', 'max:255'],
            'settings.max_recommendations' => ['nullable', 'integer', 'min:1', 'max:100'],
            'settings.confidence_threshold' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }

    public function mount(): void
    {
        $service = app(SettingsService::class);
        $this->settings = [
            'ai_enabled' => (bool) $service->cached('ai_enabled', false),
            'api_endpoint' => $service->cached('api_endpoint', ''),
            'model_name' => $service->cached('model_name', ''),
            'max_recommendations' => (int) $service->cached('max_recommendations', 5),
            'confidence_threshold' => (float) $service->cached('confidence_threshold', 0.7),
        ];
        $this->api_key = $service->cached('api_key', '');
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->settings;

        if (! empty($this->api_key)) {
            $data['api_key'] = $this->api_key;
        }

        app(SettingsService::class)->updateSettings('ai', $data, ['api_key']);

        $this->dispatch('notify', message: 'AI settings saved successfully.', type: 'success');
    }

    public function render()
    {
        return view('settings::livewire.ai-settings');
    }
}
