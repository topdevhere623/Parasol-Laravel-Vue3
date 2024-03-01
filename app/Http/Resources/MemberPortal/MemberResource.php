<?php

namespace App\Http\Resources\MemberPortal;

use App\Models\Member\Member;
use App\Models\Program;
use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Member */
class MemberResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'member_id' => $this->member_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'recovery_email' => $this->recovery_email,
            'start_date' => app_date_format($this->start_date),
            'end_date' => app_date_format($this->end_date),
            'avatar' => $this->avatar,
            'dob' => app_date_format($this->dob),
            'membership_status' => $this->membership_status,
            'member_type' => $this->member_type,
            'phone' => $this->phone,
            'pass_url' => $this->when($this->isActive(), $this->passKit?->passUrl),
            'membership_type' => $this->membershipType?->title,
            'corporate_name' => $this->corporate_name,
            'isProcessing' => $this->when(
                $this->membership_status == Member::MEMBERSHIP_STATUSES['processing'],
                fn () => $this->membershipRenewals()->completed()->count() ? 'rejoin' : 'join'
            ),
            'renewal_url' => $this->when(
                !!$this->pendingMembershipRenewal,
                $this->pendingMembershipRenewal?->renewal_url
            ),
            'portal' => MemberPortalResource::make($this->resource),
            'location' => $this->location,
            'kids' => KidResource::collection($this->whenLoaded('kids')),
            'coupon' => $this->when(
                $this->hasAccess('referrals'),
                fn () => CouponResource::make($this->whenLoaded('coupon'))
            ),
            'visiting_family_membership' => $this->when(
                $this->showVisitingFamilyMembership(),
                fn () => PurchaseResource::make($this->resource)
            ),

            'subscription_detail' => $this->when(
                !!$this->memberPortalPaymentSchedule,
                fn () => SubscriptionDetailResource::make($this->memberPortalPaymentSchedule)
            ),
        ];

        return array_merge($data, $this->imageArray());
    }

    public function showVisitingFamilyMembership()
    {
        // TODO: refactor this
        if ($this->program_id == Program::ENTERTAINER_SOLEIL_ID && !in_array($this->plan_id, [281, 282])) {
            return false;
        }
        return $this->hasAccess('visiting_family_membership') && $this->plan->is_family_plan_available;
    }
}
