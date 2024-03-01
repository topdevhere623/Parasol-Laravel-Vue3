<?php

namespace App\ParasolCRMV2\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\Filter;

class CouponOwnerFilter extends Filter
{
    public bool $isHidden = true;

    /**
     * Filter handler
     */
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        $builder->where(function (Builder $builder) {
            $builder->where('members.member_id', 'like', '%'.$this->field->getValue().'%');
            $builder->orWhere('backoffice_users.first_name', 'like', '%'.$this->field->getValue().'%');
            $builder->orWhere('backoffice_users.last_name', 'like', '%'.$this->field->getValue().'%');
        });
    }
}
