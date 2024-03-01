<?php

namespace App\ParasolCRMV2\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Filter;

class ReportMonthlySaleDateFilter extends BetweenFilter
{
    public bool $isHidden = true;

    /**
     * Filter handler
     */
    public function apply(Builder $builder, $value): void
    {
        // Filter will be applied straight on Resource
    }
}
