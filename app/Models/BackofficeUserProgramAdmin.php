<?php

namespace App\Models;

use App\Models\Laratrust\Team;
use Illuminate\Database\Eloquent\Relations\Relation;

class BackofficeUserProgramAdmin extends BackofficeUser
{
    public const TEAM = Team::TEAM_IDS['program_admins'];

    protected static function boot()
    {
        parent::boot();

        Relation::morphMap([
            BackofficeUser::class => BackofficeUserProgramAdmin::class,
        ]);

        self::created(function (BackofficeUserProgramAdmin $model) {
            $model->attachRole('hsbc_report');
        });
    }
}
