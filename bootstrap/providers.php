<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Catalog\Providers\CatalogServiceProvider::class,
    App\Modules\Circulation\Providers\CirculationServiceProvider::class,
    App\Modules\Communication\Providers\CommunicationServiceProvider::class,
    App\Modules\DigitalLibrary\Providers\DigitalLibraryServiceProvider::class,
    App\Modules\Finance\Providers\FinanceServiceProvider::class,
    App\Modules\Members\Providers\MembersServiceProvider::class,
    App\Modules\Notifications\Providers\NotificationsServiceProvider::class,
    App\Modules\Settings\Providers\SettingsServiceProvider::class,
    App\Modules\Reports\Providers\ReportsServiceProvider::class,
    App\Modules\API\Providers\ApiServiceProvider::class,
    App\Modules\Assignments\Providers\AssignmentsServiceProvider::class,
];
