<?php

namespace App\Scopes;

use App\Models\BackofficeUser;
use App\Models\Club\Club;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ClubManagerScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (\Auth::hasUser() && \Auth::user() instanceof BackofficeUser && \Auth::user()->hasTeam('club_admins')) {
            if ($model instanceof Club) {
                $builder->where($model->getTable().'.id', '=', auth()->user()->club_id);
            } else {
                $builder->where('club_id', '=', auth()->user()->club_id);
            }
        }
    }
}
