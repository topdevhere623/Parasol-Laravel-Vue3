<?php

namespace App\ParasolCRM\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Filter;

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
