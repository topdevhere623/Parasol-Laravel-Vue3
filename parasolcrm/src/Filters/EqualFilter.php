<?php

namespace ParasolCRM\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class EqualFilter
 *
 * @package ParasolCRM\Filters
 */
class EqualFilter extends Filter
{
    /**
     * Filter handler
     *
     * @param  Builder  $builder
     * @param $value
     *
     * @return void
     */
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        $builder->where($this->column, $this->field->getValue());
    }
}
