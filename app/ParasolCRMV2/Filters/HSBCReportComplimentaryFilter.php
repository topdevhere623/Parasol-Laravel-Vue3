<?php

namespace App\ParasolCRMV2\Filters;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\Filter;

class HSBCReportComplimentaryFilter extends Filter
{
    /**
     * Filter handler
     */
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);

        $builder->where(function ($query) use ($value) {
            if ($value == '1') {
                $query->where($this->column, Plan::HSBC_SINGLE_FREE);
            } elseif ($value == '2') {
                $query->where($this->column, '!=', Plan::HSBC_SINGLE_FREE);
            }
        });
    }
}
