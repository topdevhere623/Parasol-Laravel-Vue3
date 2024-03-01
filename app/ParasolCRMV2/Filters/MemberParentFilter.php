<?php

namespace App\ParasolCRMV2\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\Filter;

class MemberParentFilter extends Filter
{
    public bool $isHidden = true;

    /**
     * Filter handler
     */
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        $builder->where(function ($query) use ($value) {
            $query->where($this->column.'.id', $value)
                ->orWhere($this->column.'.parent_id', $value);
        });
    }
}
