<?php

namespace App\Observers;

use App\Models\Club\ClubTag;
use Illuminate\Support\Str;

class ClubTagObserver
{
    public function saving(ClubTag $clubTag)
    {
        $clubTag->slug = Str::slugExtended($clubTag->slug ?: $clubTag->name);
    }
}
