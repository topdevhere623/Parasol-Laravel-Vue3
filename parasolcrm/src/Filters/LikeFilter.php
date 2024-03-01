<?php

namespace ParasolCRM\Filters;

use Illuminate\Database\Eloquent\Builder;

class LikeFilter extends Filter
{
    /**
     * Filter handler
     */
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        $builder->where($this->column, 'like', '%'.$this->field->getValue().'%');
    }
}
