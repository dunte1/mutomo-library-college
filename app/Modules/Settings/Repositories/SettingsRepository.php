<?php

namespace App\Modules\Settings\Repositories;

use App\Modules\Settings\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class SettingsRepository
{
    public function getByGroup(string $group): Collection
    {
        return Setting::byGroup($group)->get();
    }

    public function set(string $key, mixed $value, string $group = 'general'): Setting
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public function has(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    public function getAll(): Collection
    {
        return Setting::all();
    }
}
