<?php

namespace ParasolCRMV2\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class InFilter
 *
 * @package ParasolCRMV2\Filters
 */
class NotInFilter extends InFilter
{
    /**
     * Filter handler
     *
     * @param Builder $builder
     * @param $value
     *
     * @return void
     */
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        $legalValue = $this->getLegalValue($this->field->getValue());
        if (count($legalValue)) {
            $builder->whereNotIn($this->column, $legalValue);
        }
    }
}
