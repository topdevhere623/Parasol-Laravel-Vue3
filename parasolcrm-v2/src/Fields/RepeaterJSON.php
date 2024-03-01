<?php

namespace ParasolCRMV2\Fields;

class RepeaterJSON extends Field
{
    public string $component = 'RepeaterJsonField';

    public function getValue()
    {
        $value = parent::getValue();

        $result = json_decode($value, true);

        return $result ?? [''];
    }

    public function setValue($value): Field
    {
        parent::setValue(json_encode($value));

        return $this;
    }
}
