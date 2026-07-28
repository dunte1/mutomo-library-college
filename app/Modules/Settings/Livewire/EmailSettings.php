<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class EmailSettings extends Component
{
    public array $settings = [];

    public bool $saved = false;

    public ?string $password = null;

    public ?string $testEmail = null;

    public bool $testing = false;

    public ?string $testResult = null;

    public bool $testSuccess = false;

    public bool $hasPassword = false;

    protected $rules = [
        'settings.mail_from_name' => 'required|string|max:255',
        'settings.mail_from_address' => 'required|email|max:255',
        'settings.mail_driver' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark,log',
        'settings.mail_host' => 'nullable|string|max:255',
        'settings.mail_port' => 'nullable|string|max:10',
        'settings.mail_encryption' => 'nullable|string|in:tls,ssl,null',
        'settings.mail_username' => 'nullable|string|max:255',
    ];

    public function sendTestEmail(): void
    {
        $this->validateOnly('testEmail', ['testEmail' => 'required|email']);
        $this->testing = true;
        $this->testResult = null;
        $this->testSuccess = false;

        $service = app(SettingsService::class);

        try {
            config([
                'mail.mailers.smtp.host' => $this->settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => $this->settings['mail_port'] ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username' => $this->settings['mail_username'] ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $service->getEmailPassword() ?: config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption' => $this->settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
                'mail.from.address' => $this->settings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $this->settings['mail_from_name'] ?? config('mail.from.name'),
            ]);

            Mail::raw('This is a test email from your library management system.', function ($message) {
                $message->to($this->testEmail)
                    ->subject('Test Email from '.config('app.name'));
            });
            $this->testSuccess = true;
            $this->testResult = 'Test email sent successfully to '.$this->testEmail;
        } catch (\Throwable $e) {
            $this->testSuccess = false;
            $this->testResult = 'Failed: '.$e->getMessage();
        }

        $this->testing = false;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
        $service = app(SettingsService::class);
        $this->settings = $service->getEmailSettings();
        $this->hasPassword = $service->hasEmailPassword();
    }

    public function save(): void
    {
        $this->validate();

        $service = app(SettingsService::class);

        $data = $this->settings;

        if (! empty($this->password)) {
            $data['mail_password'] = $this->password;
        }

        $service->updateSettings('email', $data, ['mail_password']);

        $this->hasPassword = ! empty($data['mail_password'] ?? $service->hasEmailPassword());
        $this->saved = true;
        session()->flash('success', 'Email settings saved successfully.');
    }

    public function render()
    {
        return view('settings::livewire.email-settings');
    }
}
