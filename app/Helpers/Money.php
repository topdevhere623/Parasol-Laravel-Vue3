<?php

if (!function_exists('money_formatter')) {
    function money_formatter($money): string
    {
        return number_format($money ?? 0, 2);
    }
}
