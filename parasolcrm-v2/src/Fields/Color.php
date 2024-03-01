<?php

namespace ParasolCRMV2\Fields;

class Color extends Field
{
    public string $component = 'ColorField';

    public function __construct($name, $label = null, $attrs = null)
    {
        parent::__construct($name, $label, $attrs);
    }

    public function emptyColor($color): self
    {
        $this->withMeta(['empty_color' => $color]);
        return $this;
    }
}
