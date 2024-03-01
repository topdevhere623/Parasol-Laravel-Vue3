<?php

namespace ParasolCRMV2\Fields;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

abstract class DateTimeBase extends Field
{
    public function min($min): self
    {
        $this->withMeta([__FUNCTION__ => $min]);
        return $this;
    }

    public function max($max): self
    {
        $this->withMeta([__FUNCTION__ => $max]);
        return $this;
    }

    public function setValue($value): self
    {
        $this->value = $this->carbonSafeParse($value, config('app.DATETIME_FORMAT'));

        return $this;
    }

    protected function carbonSafeParse($value, $format)
    {
        try {
            return $value ? Carbon::parse($value)->format($format) : $value;
        } catch (InvalidFormatException $exception) {
            return;
        }
    }
}
