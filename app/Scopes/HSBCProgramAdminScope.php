<?php

namespace App\Scopes;

use App\Models\BackofficeUser;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HSBCProgramAdminScope implements Scope
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
        if (
            \Auth::hasUser()
            && \Auth::user() instanceof BackofficeUser
            && \Auth::user()->hasTeam('program_admins')
            && \Auth::user()->program_id == Program::ENTERTAINER_HSBC
        ) {
            (new HSBCComplimentaryPlanScope())->apply($builder, $model);
        }
    }
}
