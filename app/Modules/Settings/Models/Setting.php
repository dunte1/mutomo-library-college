<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value', 'group', 'type', 'options', 'description', 'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_encrypted' => 'boolean',
        ];
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value;
    }

    public function setValueAttribute(mixed $value): void
    {
        if ($this->is_encrypted && $value !== null) {
            $this->attributes['value'] = Crypt::encryptString((string) $value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    public function getValueAttribute(mixed $value): mixed
    {
        if ($this->is_encrypted && $value !== null) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        if ($value === null) {
            return null;
        }

        return match ($this->type ?? 'text') {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'number' => is_numeric($value) ? (int) $value : $value,
            'float' => is_numeric($value) ? (float) $value : $value,
            default => (string) $value,
        };
    }
}
