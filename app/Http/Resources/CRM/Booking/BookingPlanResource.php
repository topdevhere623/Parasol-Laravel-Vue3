<?php

namespace App\Http\Resources\CRM\Booking;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Plan */
class BookingPlanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
