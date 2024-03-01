<?php

namespace ParasolCRMV2\Fields;

class Date extends DateTimeBase
{
    public string $component = 'DateField';

    public function displayValue($record)
    {
        return $this->carbonSafeParse($record->{$this->column}, config('app.DATE_FORMAT'));
    }

    public function getValue()
    {
        return $this->carbonSafeParse($this->value, config('app.DATE_FORMAT'));
    }

    public function setValue($value): self
    {
        $this->value = $this->carbonSafeParse($value, 'Y-m-d');

        return $this;
    }
}
