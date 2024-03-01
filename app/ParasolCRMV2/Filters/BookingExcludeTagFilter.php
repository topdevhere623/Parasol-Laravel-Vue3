<?php

namespace App\ParasolCRMV2\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\InFilter;

class BookingExcludeTagFilter extends InFilter
{
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        $legalValue = $this->getLegalValue($this->field->getValue());
        if (count($legalValue)) {
            //            $builder->whereNotExists(function ($query) use ($legalValue) {
            //                $query->whereIn('lead_tag_id', $legalValue);
            //            });

            $builder->whereNotExists(function ($query) {
                $query->from('lead_lead_tag')
                    ->whereColumn('lead_id', 'leads.id')
                    ->whereIn('lead_tag_id', $this->getLegalValue($this->field->getValue()))
                    ->select(\DB::raw('1'));
            });
        }
    }
}
