<?php

namespace App\Http\Resources\MemberPortal\Referrals;

use App\Models\Member\Member;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Member */
class ReferralRewardOptionResource extends JsonResource
{
    public const REWARDS_CAPTIONS = [
        Referral::REWARDS['cashback'] => 'Cashback (150 AED)',
        Referral::REWARDS['additional_month'] => 'Additional Membership Month',
        Referral::REWARDS['additional_club'] => 'Additional Club',
    ];

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $member = Member::find($this->resource->id);
        $programRewards = json_decode($member->program->rewards);
        $rewards = array_intersect_key(static::REWARDS_CAPTIONS, array_flip($programRewards));
        return [
            'rewards' => $rewards,
            'additional_club_available' => $this->referrals()->rewardAvailable()->count() > 2,
        ];
    }
}
