<?php

namespace App\Actions\Member;

use App\Models\Member\Member;
use App\Models\Member\MembershipRenewal;

class GetOrCreatePendingRenewalMemberAction
{
    public function handle(Member $member)
    {
        if ($member->pendingMembershipRenewal) {
            return $member->pendingMembershipRenewal;
        }

        $pendingMembershipRenewal = new MembershipRenewal();

        $pendingMembershipRenewal->end_date = $member->end_date;
        $pendingMembershipRenewal->member()->associate($member);
        $pendingMembershipRenewal->oldPlan()->associate($member->plan_id);
        $pendingMembershipRenewal->save();

        return $pendingMembershipRenewal;
    }
}
