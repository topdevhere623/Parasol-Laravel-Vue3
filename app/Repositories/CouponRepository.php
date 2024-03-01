<?php

namespace App\Repositories;

use App\Models\Channel;
use App\Models\Coupon;
use App\Models\Member\Member;

class CouponRepository extends Repository
{
    public static function createDefaultCoupon(Member $member, string $status): Coupon
    {
        $program = $member->program;
        $coupon = Coupon::create([
            'status' => $status,
            'code' => $program?->referral_code_template ? self::generateCodeFromTemplate(
                $program->referral_code_template
            ) : Coupon::generateCode(),
            'type' => Coupon::TYPES['referral'],
            'amount' => $program->referral_amount ?? Coupon::DEFAULT_AMOUNT,
            'amount_type' => $program->referral_amount_type ?? Coupon::AMOUNT_TYPES['percentage'],
            'usage_limit' => Coupon::DEFAULT_LIMIT,
            'expiry_date' => $member->end_date,
            'channel_id' => Channel::getIdByTitle(Channel::MEMBER_REFERRAL_NAME),
            'couponable_id' => $member->id,
            'couponable_type' => Coupon::COUPONABLE_TYPES['member'],
        ]);
        if ($program->has_access_referrals) {
            if ($includedPlanIds = $program->includedPlans()->pluck('plan_id')->toArray()) {
                $coupon->belongsToPlans()->attach($includedPlanIds, ['type' => 'include']);
            } elseif ($excludedPlanIds = $program->excludedPlans()->pluck('plan_id')->toArray()) {
                $coupon->belongsToPlans()->attach($excludedPlanIds, ['type' => 'exclude']);
            }
        }
        return $coupon;
    }

    public static function generateCodeFromTemplate(string $codeTemplate): string
    {
        preg_match('/{(.*)}/', $codeTemplate, $matches);
        $codePrefix = str_replace($matches[0], '', $codeTemplate);
        $codeLength = $matches[1];

        return Coupon::generateCode($codeLength, $codePrefix);
    }
}
