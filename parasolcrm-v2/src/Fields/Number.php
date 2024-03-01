<?php

namespace ParasolCRMV2\Fields;

class Number extends Field
{
    public string $component = 'NumberField';

    public ?float $step = null;
    public ?float $min = null;
    public ?float $max = null;
    public ?string $currency = null;

    public function step($step): self
    {
        $this->step = $step;
        return $this;
    }

    public function min($min): self
    {
        $this->min = $min;
        return $this;
    }

    public function max($max): self
    {
        $this->max = $max;
        return $this;
    }

    public function currency($currency): self
    {
        $this->currency = $currency;
        return $this;
    }
}
