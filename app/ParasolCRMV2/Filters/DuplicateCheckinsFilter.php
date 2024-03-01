<?php

namespace App\ParasolCRMV2\Filters;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\Filter;

class DuplicateCheckinsFilter extends Filter
{
    public function apply(Builder $builder, $value): void
    {
        $this->field->setValue($value);
        if ($value) {
            $builder->where(
                fn (Builder $query) => $query->whereNotNull($this->column)
                    ->orWhereExists(
                        fn ($query) => $query->from('checkins as duplicate_for')
                            ->whereNull('duplicate_for.deleted_at')
                            ->whereNotNull('duplicate_for.multi_checkin_id')
                            ->whereRaw('duplicate_for.multi_checkin_id = checkins.id')
                    )
            );
        }
    }
}
