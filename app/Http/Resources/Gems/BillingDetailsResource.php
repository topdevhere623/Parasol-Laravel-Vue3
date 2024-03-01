<?php

namespace App\Http\Resources\Gems;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\MemberBillingDetail */
class BillingDetailsResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company_name' => $this->company_name,
            'country' => optional($this->country)->country_name,
            'city' => $this->city,
            'state' => $this->state,
            'street' => $this->street,
        ];
    }
}
