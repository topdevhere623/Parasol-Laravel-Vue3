<?php

namespace App\ParasolCRM\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Filters\InFilter;

class MemberClubsFilter extends InFilter
{
    public function apply(Builder $builder, $value): void
    {
        if ($value) {
            $builder->leftJoin('member_club', 'members.id', '=', 'member_club.member_id');
        }
        parent::apply($builder, $value);
    }
}
