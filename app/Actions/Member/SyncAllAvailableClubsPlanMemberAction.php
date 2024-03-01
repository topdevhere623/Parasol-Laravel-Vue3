<?php

namespace App\Actions\Member;

use App\Models\Member\Member;
use App\Models\Plan;

class SyncAllAvailableClubsPlanMemberAction
{
    public function handle(Member $member, array $additionalClubs = [])
    {
        if ($member->plan?->allowed_club_type == Plan::ALLOWED_CLUB_TYPES['all_available']) {
            $member->clubs()->syncWithoutDetaching(
                array_merge($member->plan->availableClubs()->pluck('id')->toArray(), $additionalClubs)
            );
        }
    }
}
