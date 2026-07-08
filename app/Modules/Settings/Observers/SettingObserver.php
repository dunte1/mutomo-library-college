<?php

namespace App\Modules\Settings\Observers;

use App\Modules\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    public function created(Setting $setting): void
    {
        Cache::forget("setting.{$setting->key}");
        Cache::forget("setting_group.{$setting->group}");
    }

    public function updated(Setting $setting): void
    {
        Cache::forget("setting.{$setting->key}");
        Cache::forget("setting_group.{$setting->group}");
    }

    public function deleted(Setting $setting): void
    {
        Cache::forget("setting.{$setting->key}");
        Cache::forget("setting_group.{$setting->group}");
    }

    public function saved(Setting $setting): void
    {
        Cache::forget("setting.{$setting->key}");
        Cache::forget("setting_group.{$setting->group}");
    }
}
