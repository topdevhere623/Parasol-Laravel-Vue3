<?php

namespace App\Http\Resources\Program;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Booking */
class MemberBookingShowResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->old_id,
            'referance_id' => $this->reference_id,
            'total_price' => $this->total_price,
            'user_id' => $this->member->old_id,
            'status' => 'complete',
            'subtotal' => $this->subtotal_amount,
            'tax' => $this->vat_amount,
            'name' => $this->name,
            'membership_source' => $this->membership_source,
            'membership_source_other' => $this->membership_source_other,
            'email' => $this->email,

        ];
    }
}
