<?php

use App\Modules\Settings\Livewire\AccessLevelList;
use App\Modules\Settings\Livewire\AiSettings;
use App\Modules\Settings\Livewire\AppearanceSettings;
use App\Modules\Settings\Livewire\AuditLogViewer;
use App\Modules\Settings\Livewire\AuthCarouselSettings;
use App\Modules\Settings\Livewire\BackupSettings;
use App\Modules\Settings\Livewire\CacheManager;
use App\Modules\Settings\Livewire\CirculationSettings;
use App\Modules\Settings\Livewire\DepartmentForm;
use App\Modules\Settings\Livewire\DepartmentList;
use App\Modules\Settings\Livewire\DigitalLibrarySettings;
use App\Modules\Settings\Livewire\EmailSettings;
use App\Modules\Settings\Livewire\FeatureForm;
use App\Modules\Settings\Livewire\FeatureList;
use App\Modules\Settings\Livewire\GeneralSettings;
use App\Modules\Settings\Livewire\LandingPageSettings;
use App\Modules\Settings\Livewire\LocalizationSettings;
use App\Modules\Settings\Livewire\MaintenanceSettings;
use App\Modules\Settings\Livewire\NewsletterSubscriberList;
use App\Modules\Settings\Livewire\NotificationSettings;
use App\Modules\Settings\Livewire\ProgramForm;
use App\Modules\Settings\Livewire\ProgramList;
use App\Modules\Settings\Livewire\QueueMonitor;
use App\Modules\Settings\Livewire\RoleForm;
use App\Modules\Settings\Livewire\RoleList;
use App\Modules\Settings\Livewire\SecurityDashboard;
use App\Modules\Settings\Livewire\SecuritySettings;
use App\Modules\Settings\Livewire\SettingsDashboard;
use App\Modules\Settings\Livewire\StorageManager;
use App\Modules\Settings\Livewire\SubscriptionSettings;
use App\Modules\Settings\Livewire\SystemHealth;
use App\Modules\Settings\Livewire\SystemLogViewer;
use App\Modules\Settings\Livewire\TestimonialForm;
use App\Modules\Settings\Livewire\TestimonialList;
use App\Modules\Settings\Livewire\UserForm;
use App\Modules\Settings\Livewire\UserList;
use App\Modules\Settings\Livewire\WhyChooseUsForm;
use App\Modules\Settings\Livewire\WhyChooseUsList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', SettingsDashboard::class)->name('index')->middleware('permission:manage-settings');
    Route::get('/general', GeneralSettings::class)->name('general')->middleware('permission:manage-settings');
    Route::get('/circulation', CirculationSettings::class)->name('circulation')->middleware('permission:manage-settings');
    Route::get('/digital-library', DigitalLibrarySettings::class)->name('digital-library')->middleware('permission:manage-settings');
    Route::get('/notifications', NotificationSettings::class)->name('notifications')->middleware('permission:manage-settings');
    Route::get('/security', SecuritySettings::class)->name('security')->middleware('permission:manage-settings');
    Route::get('/security/dashboard', SecurityDashboard::class)->name('security.dashboard')->middleware('permission:manage-settings');
    Route::get('/email', EmailSettings::class)->name('email')->middleware('permission:manage-settings');
    Route::get('/backup', BackupSettings::class)->name('backup')->middleware('permission:manage-settings');
    Route::get('/localization', LocalizationSettings::class)->name('localization')->middleware('permission:manage-settings');
    Route::get('/appearance', AppearanceSettings::class)->name('appearance')->middleware('permission:manage-settings');
    Route::get('/audit-logs', AuditLogViewer::class)->name('audit-logs')->middleware('permission:view-audit-logs');
    Route::get('/system-logs', SystemLogViewer::class)->name('system-logs')->middleware('permission:view-system-logs');
    Route::get('/ai-settings', AiSettings::class)->name('ai-settings')->middleware('permission:manage-ai-settings');
    Route::get('/maintenance', MaintenanceSettings::class)->name('maintenance')->middleware('permission:manage-maintenance');
    Route::get('/system-health', SystemHealth::class)->name('system-health')->middleware('permission:view-system-health');
    Route::get('/queue-monitor', QueueMonitor::class)->name('queue-monitor')->middleware('permission:view-system-health');
    Route::get('/cache', CacheManager::class)->name('cache')->middleware('permission:view-system-health');
    Route::get('/storage', StorageManager::class)->name('storage')->middleware('permission:view-system-health');
    Route::get('/subscriptions', SubscriptionSettings::class)->name('subscriptions')->middleware('permission:manage-settings');
    Route::get('/auth-carousel', AuthCarouselSettings::class)->name('auth-carousel')->middleware('permission:manage-settings');
    Route::get('/landing-page', LandingPageSettings::class)->name('landing-page')->middleware('permission:manage-settings');
    Route::get('/features', FeatureList::class)->name('features')->middleware('permission:manage-settings');
    Route::get('/features/create', FeatureForm::class)->name('features.create')->middleware('permission:manage-settings');
    Route::get('/features/{id}/edit', FeatureForm::class)->name('features.edit')->middleware('permission:manage-settings');
    Route::get('/why-choose-us', WhyChooseUsList::class)->name('why-choose-us')->middleware('permission:manage-settings');
    Route::get('/why-choose-us/create', WhyChooseUsForm::class)->name('why-choose-us.create')->middleware('permission:manage-settings');
    Route::get('/why-choose-us/{id}/edit', WhyChooseUsForm::class)->name('why-choose-us.edit')->middleware('permission:manage-settings');
    Route::get('/testimonials', TestimonialList::class)->name('testimonials')->middleware('permission:manage-settings');
    Route::get('/testimonials/create', TestimonialForm::class)->name('testimonials.create')->middleware('permission:manage-settings');
    Route::get('/testimonials/{id}/edit', TestimonialForm::class)->name('testimonials.edit')->middleware('permission:manage-settings');

    Route::get('/newsletter-subscribers', NewsletterSubscriberList::class)->name('newsletter-subscribers')->middleware('permission:manage-settings');

    Route::get('/programs', ProgramList::class)->name('programs')->middleware('permission:manage-settings');
    Route::get('/programs/create', ProgramForm::class)->name('programs.create')->middleware('permission:manage-settings');
    Route::get('/programs/{id}/edit', ProgramForm::class)->name('programs.edit')->middleware('permission:manage-settings');

    Route::get('/departments', DepartmentList::class)->name('departments')->middleware('permission:manage-departments');
    Route::get('/departments/create', DepartmentForm::class)->name('departments.create')->middleware('permission:manage-departments');
    Route::get('/departments/{id}/edit', DepartmentForm::class)->name('departments.edit')->middleware('permission:manage-departments');

    Route::get('/access-levels', AccessLevelList::class)->name('access-levels')->middleware('permission:manage-access-levels');

    Route::get('/users', UserList::class)->name('users')->middleware('permission:manage-settings');
    Route::get('/users/create', UserForm::class)->name('users.create')->middleware('permission:manage-settings');
    Route::get('/users/{id}/edit', UserForm::class)->name('users.edit')->middleware('permission:manage-settings');

    Route::get('/roles', RoleList::class)->name('roles')->middleware('permission:manage-roles');
    Route::get('/roles/create', RoleForm::class)->name('roles.create')->middleware('permission:manage-roles');
    Route::get('/roles/{id}/edit', RoleForm::class)->name('roles.edit')->middleware('permission:manage-roles');
});
