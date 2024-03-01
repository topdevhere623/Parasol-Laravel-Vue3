<?php

namespace ParasolCRMV2\Fields;

class Mask extends Field
{
    public string $component = 'MaskField';

    public function mask($mask): self
    {
        $this->setAttrs(['mask' => $mask]);
        return $this;
    }
}
