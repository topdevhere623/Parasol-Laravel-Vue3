<?php

namespace App\Http\Resources\MemberPortal\Referrals;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Club\Club */
class ReferralRewardClubResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
        ];
    }
}
