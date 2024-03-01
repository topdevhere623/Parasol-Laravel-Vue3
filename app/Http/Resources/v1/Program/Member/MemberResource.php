<?php

namespace App\Http\Resources\v1\Program\Member;

use App\Models\Member\Member;
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
            'membership_number' => $this->member_id,
            'member_type' => $this->member_type,
            'membership_status' => $this->membership_status,
            'parent_membership_number' => $this->when($this->parent_id, fn () => $this->parent->member_id),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'recovery_email' => $this->when(!!$this->recovery_email, $this->recovery_email),
            'phone' => str_replace(' ', '', $this->phone),
            'start_date' => $this->start_date->toDateString(),
            'expiry_date' => $this->end_date->toDateString(),
            'photo' => file_url($this->resource, 'avatar', 'original'),
            'clubs' => $this->clubs->pluck('uuid'),
        ];

        if ($this->member_type == Member::MEMBER_TYPES['member']) {
            $data['partner'] = $this->partner?->member_id;
            $data['juniors'] = $this->juniors->isNotEmpty() ? $this->juniors->pluck('member_id') : [];
            $data['kids'] = KidResource::collection($this->kids);
        }

        return $data;
    }
}
