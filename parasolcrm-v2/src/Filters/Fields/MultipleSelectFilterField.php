<?php

namespace ParasolCRMV2\Filters\Fields;

use ParasolCRMV2\Fields\Selectable;

class MultipleSelectFilterField extends FilterField
{
    use Selectable;

    /**
     * Front component name
     *
     * @var string
     */
    protected string $component = 'MultipleSelectField';
}
