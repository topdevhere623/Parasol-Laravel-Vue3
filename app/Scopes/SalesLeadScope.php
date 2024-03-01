<?php

namespace App\Scopes;

use App\Models\BackofficeUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SalesLeadScope implements Scope
{
    protected int $programId = 0;

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (
            \Auth::hasUser()
            && \Auth::user() instanceof BackofficeUser
            && \Auth::user()->hasRole('sales')
        ) {
            $builder->where('backoffice_user_id', \Auth::user()->id);
        }
    }
}
