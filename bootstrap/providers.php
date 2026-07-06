<?php

use App\Modules\API\Providers\ApiServiceProvider;
use App\Modules\Assignments\Providers\AssignmentsServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\Catalog\Providers\CatalogServiceProvider;
use App\Modules\Circulation\Providers\CirculationServiceProvider;
use App\Modules\Communication\Providers\CommunicationServiceProvider;
use App\Modules\DigitalLibrary\Providers\DigitalLibraryServiceProvider;
use App\Modules\Finance\Providers\FinanceServiceProvider;
use App\Modules\Members\Providers\MembersServiceProvider;
use App\Modules\Notifications\Providers\NotificationsServiceProvider;
use App\Modules\Reports\Providers\ReportsServiceProvider;
use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Modules\Subscriptions\Providers\SubscriptionsServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\ModuleServiceProvider;
use App\Providers\VoltServiceProvider;

return [
    AppServiceProvider::class,
    VoltServiceProvider::class,
    ModuleServiceProvider::class,
    AuthServiceProvider::class,
    CatalogServiceProvider::class,
    CirculationServiceProvider::class,
    CommunicationServiceProvider::class,
    DigitalLibraryServiceProvider::class,
    FinanceServiceProvider::class,
    MembersServiceProvider::class,
    NotificationsServiceProvider::class,
    SettingsServiceProvider::class,
    ReportsServiceProvider::class,
    ApiServiceProvider::class,
    AssignmentsServiceProvider::class,
    SubscriptionsServiceProvider::class,
];
