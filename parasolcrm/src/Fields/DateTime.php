<?php

namespace ParasolCRM\Fields;

class DateTime extends DateTimeBase
{
    public string $component = 'DateTimeField';

    public function displayValue($record)
    {
        return $this->carbonSafeParse($record->{$this->column}, config('app.DATETIME_FORMAT'));
    }

    public function getValue()
    {
        return $this->carbonSafeParse($this->value, config('app.DATETIME_FORMAT'));
    }
}
