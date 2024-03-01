<?php

namespace ParasolCRMV2\Filters\Fields;

use ParasolCRMV2\Fields\Selectable;

class TagSelectFilterField extends FilterField
{
    use Selectable;

    /**
     * Front component name
     *
     * @var string
     */
    protected string $component = 'TagField';
}
