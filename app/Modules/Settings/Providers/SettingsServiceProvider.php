<?php

namespace App\Modules\Settings\Providers;

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
use App\Modules\Settings\Livewire\GlobalSearch;
use App\Modules\Settings\Livewire\LandingPageSettings;
use App\Modules\Settings\Livewire\LocalizationSettings;
use App\Modules\Settings\Livewire\MaintenanceSettings;
use App\Modules\Settings\Livewire\NewsletterSubscribe;
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
use App\Modules\Settings\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'settings');

        try {
            $siteName = Setting::value('site_name');
            if ($siteName) {
                config(['app.name' => $siteName]);
            }

            $siteDescription = Setting::value('site_description');
            if ($siteDescription) {
                config(['app.description' => $siteDescription]);
            }

            $libraryAddress = Setting::value('library_address');
            if ($libraryAddress) {
                config(['app.library_address' => $libraryAddress]);
            }

            $libraryPhone = Setting::value('library_phone');
            if ($libraryPhone) {
                config(['app.library_phone' => $libraryPhone]);
            }

            $libraryEmail = Setting::value('library_email');
            if ($libraryEmail) {
                config(['app.library_email' => $libraryEmail]);
            }
        } catch (\Throwable $e) {
            Log::debug('Could not load settings from database: '.$e->getMessage());
        }

        Livewire::component('settings-dashboard', SettingsDashboard::class);
        Livewire::component('general-settings', GeneralSettings::class);
        Livewire::component('circulation-settings', CirculationSettings::class);
        Livewire::component('digital-library-settings', DigitalLibrarySettings::class);
        Livewire::component('notification-settings', NotificationSettings::class);
        Livewire::component('security-settings', SecuritySettings::class);
        Livewire::component('security-dashboard', SecurityDashboard::class);
        Livewire::component('email-settings', EmailSettings::class);
        Livewire::component('backup-settings', BackupSettings::class);
        Livewire::component('localization-settings', LocalizationSettings::class);
        Livewire::component('appearance-settings', AppearanceSettings::class);
        Livewire::component('audit-log-viewer', AuditLogViewer::class);
        Livewire::component('program-list', ProgramList::class);
        Livewire::component('program-form', ProgramForm::class);
        Livewire::component('user-list', UserList::class);
        Livewire::component('user-form', UserForm::class);
        Livewire::component('role-list', RoleList::class);
        Livewire::component('role-form', RoleForm::class);
        Livewire::component('global-search', GlobalSearch::class);
        Livewire::component('department-list', DepartmentList::class);
        Livewire::component('department-form', DepartmentForm::class);
        Livewire::component('access-level-list', AccessLevelList::class);
        Livewire::component('system-log-viewer', SystemLogViewer::class);
        Livewire::component('ai-settings', AiSettings::class);
        Livewire::component('maintenance-settings', MaintenanceSettings::class);
        Livewire::component('system-health', SystemHealth::class);
        Livewire::component('queue-monitor', QueueMonitor::class);
        Livewire::component('cache-manager', CacheManager::class);
        Livewire::component('landing-page-settings', LandingPageSettings::class);
        Livewire::component('storage-manager', StorageManager::class);
        Livewire::component('auth-carousel-settings', AuthCarouselSettings::class);
        Livewire::component('subscription-settings', SubscriptionSettings::class);
        Livewire::component('feature-list', FeatureList::class);
        Livewire::component('feature-form', FeatureForm::class);
        Livewire::component('why-choose-us-list', WhyChooseUsList::class);
        Livewire::component('why-choose-us-form', WhyChooseUsForm::class);
        Livewire::component('testimonial-list', TestimonialList::class);
        Livewire::component('testimonial-form', TestimonialForm::class);
        Livewire::component('newsletter-subscribe', NewsletterSubscribe::class);
        Livewire::component('newsletter-subscriber-list', NewsletterSubscriberList::class);
    }
}
