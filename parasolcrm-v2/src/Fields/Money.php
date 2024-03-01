<?php

namespace ParasolCRMV2\Fields;

class Money extends Number
{
    public function displayValue($record)
    {
        return money_formatter($record->{$this->column});
    }
}
