<?php

namespace App\Scopes;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HSBCComplimentaryPlanScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereIn(
            $model instanceof Plan ? 'id' : $model->getTable().'.plan_id',
            [Plan::HSBC_SINGLE_FREE, Plan::HSBC_SINGLE_FAMILY_FREE]
        );
    }
}
