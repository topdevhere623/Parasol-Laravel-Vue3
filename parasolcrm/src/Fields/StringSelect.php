<?php

namespace ParasolCRM\Fields;

class StringSelect extends Field
{
    public string $component = 'StringSelectField';

    public function getValue()
    {
        $value = parent::getValue();
        $array = explode(',', $value);
        $result = [];

        foreach ($array as $element) {
            $result[] = [
                'code' => $element,
                'name' => $element,
            ];
        }

        return $result;
    }

    public function setValue($value): Field
    {
        $array = [];

        foreach ($value as $element) {
            $array[] = $element['code'];
        }

        parent::setValue(implode(',', $array));

        return $this;
    }
}
