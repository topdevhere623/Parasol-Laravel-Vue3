<?php

namespace App\Traits;

trait ImageSizeTrait
{
    /**
     * @param array|string $size
     * @return string
     */
    protected function getSizeForPath($size): string
    {
        return is_array($size)
            ? current($size).'x'.next($size)
            : $size.'x'.$size;
    }
}
