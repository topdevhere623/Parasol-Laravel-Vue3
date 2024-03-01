<?php

namespace App\ParasolCRM\Filters;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Filters\Filter;

class HsbcRegistrationTypeFilter extends Filter
{
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        if ($value) {
            if ($value == 'complimentary') {
                $builder->whereIn($this->column, [Plan::HSBC_SINGLE_FREE, Plan::HSBC_SINGLE_FAMILY_FREE]);
            } else {
                $builder->whereNotIn($this->column, [Plan::HSBC_SINGLE_FREE, Plan::HSBC_SINGLE_FAMILY_FREE]);
            }
        }
    }
}
