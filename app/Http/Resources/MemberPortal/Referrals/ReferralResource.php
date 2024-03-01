<?php

namespace App\Http\Resources\MemberPortal\Referrals;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin \App\Models\Referral */
class ReferralResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'status_title' => Str::of($this->status)
                ->snake()
                ->replace('_', ' ')
                ->title()
                ->toString(),
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'reward' => $this->reward ? Str::of($this->reward)
                ->snake()
                ->replace('_', ' ')
                ->title()
                ->toString() : null,
            'reward_status' => $this->reward_status,
            'reward_available' => $this->isRewardAvailable(),
        ];
    }
}
