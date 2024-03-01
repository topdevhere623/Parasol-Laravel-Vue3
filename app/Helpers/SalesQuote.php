<?php

use App\Models\SalesQuote;

if (!function_exists('get_duration_data')) {
    function get_duration_data($calculated): array
    {
        $durationUnits = 'Days';
        if (15 < ($duration = $calculated['duration'])) {
            $duration = SalesQuote::daysToMonths($duration);
            $durationUnits = (1 == $duration) ? 'Month' : 'Months';
        }
        return [$durationUnits, $duration];
    }
}
