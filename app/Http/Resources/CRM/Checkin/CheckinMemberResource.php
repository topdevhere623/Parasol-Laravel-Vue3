<?php

namespace App\Http\Resources\CRM\Checkin;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Member */
class CheckinMemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'member_id' => $this->member_id,
            'avatar' => file_url($this->resource, 'avatar', 'medium'),
            'available_to_guest_fee' => $this->canCheckin(),
            'available_to_checkin' => $this->clubs_count && $this->canCheckin(),
            'checkin_id' => $this->activeCheckin,
            'membership_on_hold' => !$this->canCheckin(),
            'classes_slots' => !$this->canCheckin(),
            'kids' => CheckinKidResource::collection($this->whenLoaded('kids')),
        ];
    }
}
