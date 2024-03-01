<?php

namespace ParasolCRMV2\Filters\Fields;

use Illuminate\Support\Collection;
use ParasolCRMV2\Fields\Selectable;

class CascadeSelectFilterField extends FilterField
{
    use Selectable;
    use Selectable {
        Selectable::options as protected selectableOptions;
    }

    /**
     * Front component name
     *
     * @var string
     */
    protected string $component = 'CascadeSelectField';

    public array $depends = [];

    public function options(array|Collection $options): self
    {
        return $this->selectableOptions(['' => 'all'] + $options);
    }

    // behavior = ['show', 'hide', 'disable', 'set' => 100]
    public function dependsOn($field, $value, $behaviors = ['show']): self
    {
        $this->depends = [
            'field' => $field,
            'value' => $value,
        ];

        // Формируем массив для удобной работы с объектом на фронте
        foreach ($behaviors as $k => $v) {
            if (is_string($k)) {
                $this->depends[$k] = $v;
            } else {
                $this->depends[$v] = true;
            }
        }

        $this->withMeta([
            'depends' => $this->depends,
        ]);

        return $this;
    }
}
