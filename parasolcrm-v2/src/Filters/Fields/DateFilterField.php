<?php

namespace ParasolCRMV2\Filters\Fields;

use Carbon\Carbon;

class DateFilterField extends FilterField
{
    /**
     * Front component name
     *
     * @var string
     */
    protected string $component = 'DateField';

    public function setValue($value): self
    {
        if (!$value instanceof Carbon) {
            try {
                $value = new Carbon($value);
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                $value = '';
            }
        }

        parent::setValue($value);
        return $this;
    }

    public function getValue()
    {
        return $this->value ? $this->value->toDateString() : null;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue instanceof Carbon ? $this->defaultValue->format(config('app.DATE_FORMAT')) : null;
    }
}
