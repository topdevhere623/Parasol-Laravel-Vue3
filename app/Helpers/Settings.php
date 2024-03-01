<?php

use App\Models\Setting;

if (!function_exists('settings')) {
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $settings = Setting::allCached();

        if (is_null($key)) {
            return $settings;
        }

        try {
            return $settings[$key];
        } catch (\Exception $e) {
            return $default;
        }
    }
}
