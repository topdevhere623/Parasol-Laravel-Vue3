<?php

namespace App\ParasolCRM\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Filters\Filter;

class NonZeroCheckinsCountFilter extends Filter
{
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        if ($value === '1') {
            $builder->having($this->column, '>', 0);
        }
    }
}
