<?php

namespace App\Models\Club;

use App\Models\BackofficeUser;
use App\Models\Laratrust\Team;
use Illuminate\Database\Eloquent\Relations\Relation;

class BackofficeUserClubAdmin extends BackofficeUser
{
    public const TEAM = Team::TEAM_IDS['club_admins'];

    protected static function boot()
    {
        parent::boot();

        Relation::morphMap([
            BackofficeUser::class => BackofficeUserClubAdmin::class,
        ]);

        self::created(function (BackofficeUserClubAdmin $model) {
            $model->attachRole('club_manager');
        });
    }
}
