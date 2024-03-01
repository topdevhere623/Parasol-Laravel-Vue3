<?php

namespace ParasolCRM\Fields;

class ID extends Field
{
    public string $component = 'IdField';

    public bool $displayOnForm = false;

    public function __construct($name = null, $label = null, $attrs = null)
    {
        parent::__construct($name ?? 'id', $label ?? 'ID', $attrs);
    }
}
