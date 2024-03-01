<?php

namespace App\Models\Traits;

trait ColumnLabelTrait
{
    public function labels(): array
    {
        return [
        ];
    }

    public function getLabel(string $key): ?string
    {
        $name = lcfirst(class_basename($this)).'.'.$key;

        return app('translator')->has($name) ? trans($name) : $this->getDefaultLabel($key);
    }

    protected function getDefaultLabel(string $key)
    {
        return key_exists($key, $this->labels())
            ? $this->labels()[$key]
            : $this->getLabelFromString($key);
    }

    protected function getLabelFromString(string $key)
    {
        if (strpos($key, '.')) {
            $key = last(explode('.', $key));
        }
        return str_replace('_', ' ', trim(ucfirst($key)));
    }

    /**
     * Get options from constants with translation or default method
     *
     * @param  string  $key
     * @return array
     */
    public static function getConstOptions(string $key): array
    {
        $options = [];
        if (defined(static::class.'::'.strtoupper($key))) {
            $const = constant(static::class.'::'.strtoupper($key));
            if (is_array($const)) {
                $model = new static();
                foreach ($const as $i => $item) {
                    $options[$i] = $model->getLabel(strtolower($key).'.'.$i);
                }
            }
        }
        return $options;
    }
}
