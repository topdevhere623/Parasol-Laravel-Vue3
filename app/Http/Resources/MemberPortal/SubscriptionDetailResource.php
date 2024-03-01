<?php

namespace App\Http\Resources\MemberPortal;

use App\Models\Member\MemberPaymentSchedule;
use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\MemberPaymentSchedule */
class SubscriptionDetailResource extends JsonResource
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
            'fee' => booking_amount_round($this->monthly_amount),
            'next_invoice_date' => app_date_format($this->charge_date),
            'card_last4_digits' => $this->card_last4_digits,
            'card_expiry_date' => $this->card_expiry_date?->format('F Y'),
            'card_scheme' => $this->card_scheme,
            'debt_amount' => booking_amount_round($this->calculateChargeAmount()),
            'change' => $this->payment_method_id == 2,
            'status' => $this->card_status == MemberPaymentSchedule::CARD_STATUS['active'],
        ];

        return array_merge($data, $this->imageArray());
    }
}
