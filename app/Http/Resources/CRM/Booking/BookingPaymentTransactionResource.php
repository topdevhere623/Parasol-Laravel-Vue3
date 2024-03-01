<?php

namespace App\Http\Resources\CRM\Booking;

use App\ParasolCRM\Resources\PaymentTransactionResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payments\PaymentTransaction */
class BookingPaymentTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reference_id' => $this->payment->reference_id,
            'total_amount' => money_formatter($this->amount),
            'status' => $this->status,
            'type' => $this->type,
            'payment_id' => $this->payment_id,
            'payment_method_id' => $this->payment_method_id,
            'payment_method' => $this->paymentMethod->title,
            'status_badges' => PaymentTransactionResource::STATUS_BADGES,
            'type_badges' => PaymentTransactionResource::TYPE_BADGES,
            'created_at' => $this->created_at->format(config('app.DATETIME_FORMAT')),
        ];
    }
}
