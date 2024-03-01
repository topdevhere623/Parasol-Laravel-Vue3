<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BackofficeUserTeamScope implements Scope
{
    public function __construct(protected $teamId)
    {
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('team_id', $this->teamId);
        //        $builder->whereHas('team', function ($query) {
        //            $query->where('id', $this->teamId);
        //        });
    }
}
