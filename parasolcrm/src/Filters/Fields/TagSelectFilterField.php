<?php

namespace ParasolCRM\Filters\Fields;

use ParasolCRM\Fields\Selectable;

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
