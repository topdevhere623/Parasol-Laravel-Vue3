<?php

namespace App\Models\Traits;

trait ImageDataGettersTrait
{
    public array $dynamicImages = [];
    public array $dynamicImagesOriginal = [];

    public static function getFilePath(string $key)
    {
        return static::getFileConfigValue($key, 'path');
    }

    public static function getFileSize(string $key)
    {
        return static::getFileConfigValue($key, 'size');
    }

    public static function getFileAction(string $key)
    {
        return static::getFileConfigValue($key, 'action') ?? [];
    }

    protected static function getFileConfigValue(string $key, $value)
    {
        if (key_exists($key, static::FILE_CONFIG)) {
            if (key_exists($value, static::FILE_CONFIG[$key])) {
                return static::FILE_CONFIG[$key][$value];
            }
        }
    }
}
