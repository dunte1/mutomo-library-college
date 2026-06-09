<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    private array $modules = [
        'Auth',
        'Roles',
        'Catalog',
        'Circulation',
        'DigitalLibrary',
        'Members',
        'Finance',
        'Notifications',
        'Reports',
        'AI',
        'Communication',
        'Settings',
        'API',
        'Subscriptions',
        'Assignments',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ($this->modules as $module) {
            $modulePath = app_path("Modules/{$module}");

            // Module migrations removed — all migrations are centralized
            // in database/migrations/ for consistent ordering and naming.

            // Register translations
            $translationsPath = "{$modulePath}/Translations";
            if (is_dir($translationsPath)) {
                $this->loadTranslationsFrom($translationsPath, \Illuminate\Support\Str::kebab($module));
            }
        }
    }
}
