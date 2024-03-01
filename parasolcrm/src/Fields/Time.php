<?php

namespace ParasolCRM\Fields;

class Time extends DateTimeBase
{
    public string $component = 'TimeField';

    public function value($value): self
    {
        $this->value = $this->carbonSafeParse($value, config('app.TIME_FORMAT'));

        return $this;
    }

    public function getValue()
    {
        return $this->value = $this->carbonSafeParse($this->value, config('app.TIME_FORMAT'));
    }

    public function setValue($value): self
    {
        $this->value = $this->carbonSafeParse($value, 'H:i:s');

        return $this;
    }
}
