<?php

namespace ParasolCRMV2\Fields;

class Editor extends Field
{
    public string $component = 'EditorField';

    public bool $displayOnTable = false;

    public array $options = [];

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }
}
