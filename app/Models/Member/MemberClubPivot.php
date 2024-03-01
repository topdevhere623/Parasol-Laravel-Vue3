<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class MemberClubPivot extends MorphPivot
{
    protected $table = 'member_club';
}
