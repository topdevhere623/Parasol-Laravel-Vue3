<?php

namespace ParasolCRM\Filters\Fields;

use Illuminate\Support\Collection;
use ParasolCRM\Fields\Selectable;

class HorizontalRadioButtonFilterField extends FilterField
{
    use Selectable {
        Selectable::options as protected parentOptions;
    }

    protected array|Collection $defaultOptions = ['' => 'All'];

    /**
     * Front component name
     *
     * @var string
     */
    protected string $component = 'HorizontalRadioButtonField';

    public function options(array|Collection $options): self
    {
        return $this->parentOptions($this->defaultOptions + $options);
    }

    public function defaultOptions(array|Collection $defaultOptions): self
    {
        $this->defaultOptions = $defaultOptions;

        return $this;
    }
}
