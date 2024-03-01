<?php

namespace App\Models\Laratrust;

use Laratrust\Models\LaratrustTeam;

class Team extends LaratrustTeam
{
    // Teams mapping
    // TODO: refactor this
    public const TEAM_IDS = [
        'adv_management' => 1,
        'club_admins' => 2,
        'program_admins' => 3,
    ];

    public $guarded = [];
}
