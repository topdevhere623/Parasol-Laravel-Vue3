<?php

namespace App\Observers;

use App\Models\Club\Checkin;
use App\Models\Member\Member;
use ParasolCRM\Activities\Facades\Activity;

class CheckinObserver
{
    public function creating(Checkin $checkin)
    {
        $checkin->created_by ??= auth()->id();
        $checkin->checked_in_at = now();
        $checkin->plan_id = $checkin->member->plan_id;
        $checkin->package_id = $checkin->member->package_id;
        $checkin->program_id = $checkin->member->program_id;
    }

    public function created(Checkin $checkin)
    {
        $this->changeClubTraffic($checkin);
        $memberCheckinsLimit = $checkin->member->plan?->check_ins_limit ?? 0;

        if ($memberCheckinsLimit && $checkin->member->checkins()->count() >= $memberCheckinsLimit) {
            $member = $checkin->member;
            $member->membership_status = Member::MEMBERSHIP_STATUSES['redeemed'];
            $member->save();
        }

        $duplicateCheckin = Checkin::where('member_id', $checkin->member_id)
            ->where('id', '<', $checkin->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('created_at', '<=', now()->endOfDay())
            ->oldest()
            ->first();

        if ($duplicateCheckin) {
            $checkin->multi_checkin_id = $duplicateCheckin->id;
            $checkin->saveQuietly();
        }
    }

    public function updated(Checkin $checkin)
    {
        $this->changeClubTraffic($checkin);
    }

    public function deleted(Checkin $checkin)
    {
        $this->changeClubTraffic($checkin);
    }

    private function changeClubTraffic(Checkin $checkin): void
    {
        Activity::disable();

        $partner = $checkin->club->partner;
        if ($partner->is_pooled_access) {
            $partner->clubs()->withoutGlobalScopes()->each(function ($club) {
                $club->save();
            });
        } else {
            $checkin->club->save();
        }

        Activity::enable();
    }
}
