<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class MemberClubFavoritePivot extends MorphPivot
{
    protected $table = 'member_club_favorite';
}
