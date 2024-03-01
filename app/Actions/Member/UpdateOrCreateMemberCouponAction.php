<?php

namespace App\Actions\Member;

use App\Models\Coupon;
use App\Models\Member\Member;
use App\Repositories\CouponRepository;

class UpdateOrCreateMemberCouponAction
{
    public function handle(Member $member, bool $updatePlans = false)
    {
        $member->loadMissing('coupon', 'program');
        $coupon = $member->coupon;
        $program = $member->program;
        $status = match (true) {
            $member->trashed() => Coupon::STATUSES['member_unknown'],
            $member->isExpired() => Coupon::STATUSES['expired'],
            !$program->has_access_referrals => Coupon::STATUSES['referrals_inactive'],
            $member->isActive() => Coupon::STATUSES['active'],
            default => Coupon::STATUSES['inactive'],
        };
        if ($member->isAvailableForOwnCoupon() && !$coupon) {
            $coupon = CouponRepository::createDefaultCoupon($member, $status);
            $member->referral_code = $coupon->code;
            $member->saveQuietly();
            return;
        }
        if (!$coupon) {
            return;
        }
        $coupon
            ->update([
                'status' => $status,
                'amount' => $program->referral_amount,
                'amount_type' => $program->referral_amount_type,
                'expiry_date' => $member->end_date,
            ]);
        if ($updatePlans) {
            $coupon->belongsToPlans()->sync([]);
            if ($includedPlanIds = $program->includedPlans()->pluck('plan_id')->toArray()) {
                $coupon->belongsToPlans()->attach($includedPlanIds, ['type' => 'include']);
            } elseif ($excludedPlanIds = $program->excludedPlans()->pluck('plan_id')->toArray()) {
                $coupon->belongsToPlans()->attach($excludedPlanIds, ['type' => 'exclude']);
            }
        }
    }
}
