<?php

namespace App\Http\Resources\MemberPortal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payments\Payment */
class PaymentResource extends JsonResource
{
    /**
     * @param  Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'invoice_number' => $this->invoice_number,
            'total_amount' => money_formatter($this->total_amount).' AED',
            'status' => $this->status,
            'date' => app_date_format($this->payment_date),
        ];
    }
}
