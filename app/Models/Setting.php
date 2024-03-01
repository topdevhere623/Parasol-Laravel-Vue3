<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Setting extends Model
{
    use HasFactory;

    public const VALUE_TYPES = [
        'bool' => 'bool',
        'int' => 'int',
        'float' => 'float',
        'string' => 'string',
        'array' => 'array',
    ];

    public $fillable = ['key', 'value', 'editable', 'value_type'];

    protected static function boot()
    {
        parent::boot();

        self::saved(function () {
            \Cache::forget('settings');
        });
    }

    /**
     * Get all settings with cache
     */
    public static function allCached(): array
    {
        return app()->isProduction() && !app()->hasDebugModeEnabled() ? \Cache::rememberForever(
            'settings',
            fn () => static::getArray()
        ) : static::getArray();
    }

    public static function getArray(): array
    {
        return self::get()->mapWithKeys(function ($setting) {
            return [
                $setting->key => $setting->converted_value,
            ];
        })->toArray();
    }

    public function getConvertedValueAttribute()
    {
        $value = $this->value;

        if ($value === null) {
            return $value;
        }

        if ($this->value_type == static::VALUE_TYPES['array']) {
            $value = json_decode($value, true);
        } else {
            settype($value, $this->value_type);
        }

        return $value;
    }

    public static function updateByKey(string $key, mixed $value): static
    {
        $record = static::getByKey($key);
        $record->setAttribute('value', $value);
        $record->save();
        return $record;
    }

    public static function getValByKey(string $key)
    {
        return static::getByKey($key)->value;
    }

    public static function getByKey(string $key): static
    {
        return static::where('key', $key)->firstOr(
            fn () => throw new ModelNotFoundException('Settings key ['.$key.'] not found')
        );
    }
}
