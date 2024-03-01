<?php

namespace App\Casts;

use App\Traits\ImageSizeTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class FileCast implements CastsAttributes
{
    use ImageSizeTrait;

    protected array $defaultSizeNames = ['small', 'medium', 'large'];

    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param mixed                               $value
     * @param array                               $attributes
     *
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        $path = isset($model->galleryable_type)
            ? $model->galleryable_type::getFilePath('gallery')
            : $model::getFilePath($key);

        if (!is_array($value)) {
            $this->setImage($model, $key, $value, $path);
        }

        return $value;
    }

    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $path
     *
     * @return void
     */
    protected function setImage($model, $key, $value, $path): void
    {
        if ($value) {
            $model->dynamicImagesOriginal[$key] = \URL::uploads(trim($path, '/').'/original/'.$value);
            $model->dynamicImages[$key.'_url'] = $this->setImageSizes($model, $key, $value, $path);
        }
    }

    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $path
     */
    protected function setImageSizes($model, $key, $value, $path): array
    {
        $sizes = isset($model->galleryable_type)
            ? $model->galleryable_type::getFileSize('gallery')
            : $model::getFileSize($key);

        $sizeUrls = [];

        foreach (is_array($sizes) ? $sizes : [] as $key => $currentSize) {
            $size = $this->getSizeForPath($currentSize);
            $sizeUrls[$this->getSizeName($key)] = \URL::uploads(
                trim($path, '/').DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.$value
            );
        }

        if (count($sizeUrls) && (count($sizeUrls) != count($this->defaultSizeNames))) {
            if (!key_exists('medium', $sizeUrls) && key_exists('small', $sizeUrls)) {
                $sizeUrls['medium'] = $sizeUrls['small'];
            }
            if (!key_exists('large', $sizeUrls) && key_exists('medium', $sizeUrls)) {
                $sizeUrls['large'] = $sizeUrls['medium'];
            }
        }

        return $sizeUrls;
    }

    private function getSizeName($key): string
    {
        if (key_exists($key, $this->defaultSizeNames)) {
            return $this->defaultSizeNames[$key];
        }
        return 'custom'.($key - count($this->defaultSizeNames) + 1);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param mixed                               $value
     * @param array                               $attributes
     *
     * @return string|array
     */
    public function set($model, $key, $value, $attributes)
    {
        return [$key => $value];
    }
}
