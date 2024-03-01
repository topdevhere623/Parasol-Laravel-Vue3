<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Member */
class LeadMemberResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        $data = [
            'member_id' => $this->member_id,
            'avatar' => file_url($this->resource, 'avatar', 'medium'),
            'membership_status' => $this->membership_status,
            'pass_url' => $this->when($this->isActive(), $this->passKit?->passUrl),
            'coupon' => $this->when(
                $this->hasAccess('referrals'),
                $this->activeCoupon?->code
            ),
        ];

        return $data;
    }
}
