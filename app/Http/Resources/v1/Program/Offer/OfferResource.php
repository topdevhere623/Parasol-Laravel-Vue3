<?php

namespace App\Http\Resources\v1\Program\Offer;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Offer */
class OfferResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'logo' => file_url($this->resource, 'logo', 'medium'),
            'expiry' => optional(Carbon::parse($this->expiry_date))->format(config('app.DATE_FORMAT')),
            'emirate' => $this->emirate,
            'clubs' => $this->whenLoaded('activeClubs', $this->clubs->pluck('uuid')),
            'offer_type' => new OfferTypeResource($this->whenLoaded('offerType')),
        ];
    }
}
