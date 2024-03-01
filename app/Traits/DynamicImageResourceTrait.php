<?php

namespace App\Traits;

trait DynamicImageResourceTrait
{
    protected function imageArray(): array
    {
        $files = [];
        foreach ($this->dynamicImages as $key => $file) {
            $files[$key] = $file;
            $files[$key]['original'] = $this->dynamicImagesOriginal[str_replace('_url', '', $key)];
        }

        return $files;
    }
}
