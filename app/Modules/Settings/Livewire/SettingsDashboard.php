<?php

namespace App\Modules\Settings\Livewire;

use Livewire\Component;

class SettingsDashboard extends Component
{
    public array $groups = [];

    public function mount(): void
    {
        $allGroups = [
            [
                'title' => 'General',
                'description' => 'Configure library name, address, contact information and basic settings',
                'route' => 'settings.general',
                'color' => 'primary',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.573-1.066z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ],
            [
                'title' => 'Circulation Rules',
                'description' => 'Set borrowing limits, loan periods, renewal policies and fine rates',
                'route' => 'settings.circulation',
                'color' => 'emerald',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            ],
            [
                'title' => 'Digital Library',
                'description' => 'Manage upload limits, file type restrictions and access policies',
                'route' => 'settings.digital-library',
                'color' => 'purple',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
            ],
            [
                'title' => 'Notifications',
                'description' => 'Configure email and SMS alerts, reminders and notification preferences',
                'route' => 'settings.notifications',
                'color' => 'amber',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
            ],
            [
                'title' => 'Security',
                'description' => 'Set password policies, login attempts, session timeout and 2FA',
                'route' => 'settings.security',
                'color' => 'red',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
            ],
            [
                'title' => 'Email',
                'description' => 'Configure SMTP settings, sender details and email delivery options',
                'route' => 'settings.email',
                'color' => 'blue',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            ],
            [
                'title' => 'Backup',
                'description' => 'Schedule automatic backups, set retention and storage preferences',
                'route' => 'settings.backup',
                'color' => 'cyan',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>',
            ],
            [
                'title' => 'Localization',
                'description' => 'Set language, timezone, date format and regional preferences',
                'route' => 'settings.localization',
                'color' => 'indigo',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            [
                'title' => 'Appearance',
                'description' => 'Customize theme, logo, favicon and interface branding',
                'route' => 'settings.appearance',
                'color' => 'pink',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
            ],
            [
                'title' => 'Audit Logs',
                'description' => 'View system activity logs, track user actions and security events',
                'route' => 'settings.audit-logs',
                'color' => 'slate',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            ],
            [
                'title' => 'System Health',
                'description' => 'Monitor system status, run diagnostics, and optimize performance',
                'route' => 'settings.system-health',
                'color' => 'cyan',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            [
                'title' => 'Subscriptions',
                'description' => 'Configure subscription pricing plans and billing settings',
                'route' => 'settings.subscriptions',
                'color' => 'blue',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
            ],
        ];

        // Only include groups whose routes exist
        $this->groups = array_values(array_filter($allGroups, fn($g) => \Illuminate\Support\Facades\Route::has($g['route'])));
    }

    public function render()
    {
        return view('settings::livewire.settings-dashboard');
    }
}
