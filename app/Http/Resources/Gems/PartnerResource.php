<?php

namespace App\Http\Resources\Gems;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Partner */
class PartnerResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'gems_plus_member_id' => $this->member_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
