<?php

namespace App\Http\Resources\v1\Program\Member;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Kid */
class KidResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'parent_membership_id' => $this->when($this->parent_id, fn () => $this->member->member_id),
            'birthday' => $this->dob?->toDateString(),
        ];
    }
}
