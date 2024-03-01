<?php

namespace App\Http\Resources\Program;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\MemberShippingDetail */
class MemberShippingDetailResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->old_id ?? $this->id,
            // TODO: add booking_id
            //            'booking_id' => '',
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company_name' => $this->company_name ?? 'N/A',
            'country' => $this->country->country_name,
            'city' => $this->city,
            'state' => $this->state,
            'street' => $this->street,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
