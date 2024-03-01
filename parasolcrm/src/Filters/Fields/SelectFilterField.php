<?php

namespace ParasolCRM\Filters\Fields;

use Illuminate\Support\Collection;
use ParasolCRM\Fields\Selectable;

class SelectFilterField extends FilterField
{
    use Selectable {
        Selectable::options as protected parentOptions;
    }

    /**
     * Front component name
     *
     * @var string
     */
    protected string $component = 'SelectField';

    public function options(array|Collection $options): self
    {
        if ($options instanceof Collection) {
            $options = $options->toArray();
        }
        return $this->parentOptions(['' => 'All'] + $options);
    }
}
