<?php

namespace App\Http\Resources\Gems;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Booking */
class BookingResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'label' => $this->gemsApi->token_id,
            'transaction_id' => 'TRN'.str_pad($this->member->id, 6, '0', STR_PAD_LEFT),
            'trn_Datetime' => now()->format('d-M-y H:i:s'),
            'loyalty_id' => $this->gemsApi->loyalty_id,
            'gems_plus_id' => $this->gemsApi->member_id,
            'membership_type' => optional($this->member->membershipType)->card_title,
            'expiry' => $this->member->end_date,
            'first_name' => $this->member->first_name,
            'last_name' => $this->member->last_name,
            'phone' => $this->member->phone,
            'photo' => file_url($this->member, 'avatar', 'original'),
            'email' => $this->member->email,
            'business_email' => $this->member->recovery_email,
            'start_date' => $this->member->start_date,
            'subtotal' => $this->subtotal_amount,
            'tax' => $this->vat_amount,
            'total_price' => $this->total_price,

            'billing_details' => [
                ...BillingDetailsResource::make($this->whenLoaded('member.memberBillingDetail'))->toArray($request),
                'partner' => PartnerResource::make($this->whenLoaded('member.partner')),
            ],
        ];
    }
}
