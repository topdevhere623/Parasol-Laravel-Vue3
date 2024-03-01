<?php

namespace App\Http\Resources\v1\Program\Webhook\Booking;

use App\Http\Resources\v1\Program\Webhook\Membership\MembershipResource;
use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Booking */
class BookingResource extends JsonResource
{
    use DynamicImageResourceTrait;

    public static $wrap = null;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $member = $this->member;

        return [
            'id' => $this->uuid,
            'external_id' => $this->programApiRequest->external_id,
            'extra_params' => $this->programApiRequest->request['extra_params'] ?? null,
            'reference' => $this->reference_id,
            'plan' => $this->plan->title,
            'type' => $this->type,
            'payment' => [
                'payment_method' => $this->payment->paymentMethod->website_title,
                'plan_amount' => money_formatter($this->plan_amount),
                'extra_child_amount' => money_formatter($this->extra_child_amount),
                'extra_junior_amount' => money_formatter($this->extra_junior_amount),
                'subtotal_amount' => money_formatter($this->subtotal_amount),
                'coupon_amount' => money_formatter($this->coupon_amount),
                'gift_card_amount' => money_formatter($this->gift_card_amount),
                'vat_amount' => money_formatter($this->vat_amount),
                'total_price' => money_formatter($this->total_price),
            ],
            'billing_details' => [
                'first_name' => $member->memberBillingDetail?->first_name,
                'last_name' => $member->memberBillingDetail?->last_name,
                'company_name' => $member->corporate?->title,
                'country' => $member->memberBillingDetail?->country?->country_name,
                'city' => $member->memberBillingDetail?->city,
                'state' => $member->memberBillingDetail?->state,
                'street' => $member->memberBillingDetail?->street,
            ],
            'membership' => MembershipResource::make($member),
        ];
    }
}
