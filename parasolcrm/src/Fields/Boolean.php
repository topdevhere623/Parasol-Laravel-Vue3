<?php

namespace ParasolCRM\Fields;

class Boolean extends Field
{
    public string $component = 'BooleanField';

    protected int $trueValue = 1;

    protected int $falseValue = 0;

    public function __construct($name, $label = null, $attrs = null)
    {
        parent::__construct($name, $label, $attrs);
        $this->badges([0 => 'default', 1 => 'green']);
    }

    public function setValue($value): self
    {
        parent::setValue(filter_var($value, FILTER_VALIDATE_BOOLEAN));
        return $this;
    }

    public function fillRecord($record): self
    {
        $record->{$this->column} = $this->value ? $this->trueValue : $this->falseValue;
        return $this;
    }

    public function setFromRecord($record)
    {
        $this->value = $record->{$this->column} == $this->trueValue;
        return $this;
    }

    public function trueValue($value): self
    {
        $this->trueValue = $value;
        return $this;
    }

    public function falseValue($value): self
    {
        $this->falseValue = $value;
        return $this;
    }

    public function displayValue($record)
    {
        return $record->{$this->column} == $this->trueValue ? 'Yes' : 'No';
    }
}
