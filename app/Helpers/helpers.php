<?php

if (!function_exists('file_url')) {
    /**
     * Get file url from model.
     */
    function file_url($model, string $name, string $size = '', ?string $default = ''): ?string
    {
        $name = strtolower($name);
        $size = strtolower($size);

        if ($model && $model->{$name}) {
            if (property_exists($model, 'dynamicImages')
                && is_array($model->dynamicImages)
                && key_exists($name.'_url', $model->dynamicImages)
            ) {
                if ($size && key_exists($size, $model->dynamicImages[$name.'_url'])) {
                    return $model->dynamicImages[$name.'_url'][$size];
                }
                return $model->dynamicImagesOriginal[$name];
            }
        }

        return $default;
    }
}

if (!function_exists('route_is')) {
    /**
     * Check if route(s) is the current route.
     *
     * @param array|string $routes
     *
     * @return bool
     */
    function route_is($routes, array $params = []): bool
    {
        if (!is_array($routes)) {
            $routes = [$routes];
        }

        /** @var Illuminate\Routing\Router $router */
        $router = app('router');

        if (!count($params)) {
            return call_user_func_array([$router, 'is'], $routes);
        }

        $segments = request()->segments();
        if (is_array($segments) && is_array($params)) {
            if (in_array(end($segments), $params)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('has_filter')) {
    /**
     * Check request filter has item.
     *
     * @param string $name
     *
     * @return bool
     */
    function has_filter(string $name): bool
    {
        if (request()->has('filters')) {
            $filters = json_decode(request()->get('filters'), true);
            if (key_exists($name, $filters) && $filters[$name]) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('booking_amount_round')) {
    /**
     * Round booking price calculation values
     *
     * @param int|float $amount
     *
     * @return float
     */
    function booking_amount_round($amount): float
    {
        return round($amount, 2, PHP_ROUND_HALF_DOWN);
    }
}

if (!function_exists('date_formatter')) {
    /**
     * Format's date for whole project
     */
    function date_formatter(string|Carbon\Carbon $date): string
    {
        $date = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $date->format('d F Y');
    }
}

if (!function_exists('app_date_format')) {
    function app_date_format(null|string|Carbon\Carbon $date, ?string $default = null): ?string
    {
        return $date ? \Carbon\Carbon::parse($date)?->format(config('app.DATE_FORMAT')) : $default;
    }
}

if (!function_exists('app_datetime_format')) {
    function app_datetime_format(null|string|Carbon\Carbon $date, ?string $default = null): ?string
    {
        return $date ? \Carbon\Carbon::parse($date)?->format(config('app.DATETIME_FORMAT')) : $default;
    }
}

if (!function_exists('member_has_access')) {
    function member_has_access(string $name, ?App\Models\Member\Member $member = null): ?string
    {
        $member ??= auth()->user();
        return $member && $member->program->{'has_access_'.$name};
    }
}

if (!function_exists('get_hsbc_paid_package_url')) {
    function get_hsbc_paid_package_url(): ?string
    {
        $hsbcPaidPackage = \App\Models\Package::find(settings('hsbc_paid_checkout_package_id'), ['slug']);
        return $hsbcPaidPackage ? route('booking.step-1', [
            'package' => $hsbcPaidPackage->slug,
        ]) : null;
    }
}

if (!function_exists('css_prop')) {
    function css_prop(null|array|string $props, ?string $value = null): ?string
    {
        $props = is_array($props) ? $props : [$props => $value];
        $result = '';

        foreach ($props as $prop => $value) {
            if ($value) {
                $result .= "{$prop}: {$value};\n";
            }
        }

        return $result ?: null;
    }
}

/**
 * Increases or decreases the brightness of a color by a percentage of the current brightness.
 *
 * @param string $hexCode Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
 * @param float $adjustPercent A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
 *
 * @return  string
 * @author  maliayas
 * https://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
 */

if (!function_exists('adjust_brightness')) {
    function adjust_brightness($hexCode, $adjustPercent): string
    {
        $hexCode = ltrim($hexCode, '#');

        if (strlen($hexCode) == 3) {
            $hexCode = $hexCode[0].$hexCode[0].$hexCode[1].$hexCode[1].$hexCode[2].$hexCode[2];
        }

        $hexCode = array_map('hexdec', str_split($hexCode, 2));

        foreach ($hexCode as & $color) {
            $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
            $adjustAmount = ceil($adjustableLimit * $adjustPercent);

            $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
        }

        return '#'.implode($hexCode);
    }
}

if (!function_exists('fix_html')) {
    function fix_html(?string $value): string
    {
        if (is_null($value)) {
            return '';
        }

        $doc = new DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8'));
        $doc->encoding = 'UTF-8';
        return $doc->saveHTML();
    }
}

if (!function_exists('is_entertainer_subdomain')) {
    function is_entertainer_subdomain(): bool
    {
        $params = explode('.', request()->getHost());
        return $params[0] === 'entertainer';
    }
}

if (!function_exists('get_telegram_notifiable')) {
    function get_telegram_notifiable(): array
    {
        $notifiable = [\App\Models\System::first()];
        if (app()->isProduction()) {
            $notifiable[] = \App\Models\BackofficeUser::find(1);
        }
        return $notifiable;
    }
}
