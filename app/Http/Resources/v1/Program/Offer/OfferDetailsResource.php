<?php

namespace App\Http\Resources\v1\Program\Offer;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Offer */
class OfferDetailsResource extends JsonResource
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
            'offer_value' => $this->offer_value,
            'about' => $this->about,
            'terms' => $this->terms,
            'location' => $this->location,
            'area' => $this->area,
            'emirate' => $this->emirate,
            'website' => $this->website,
            'map_link' => $this->map,
            'offer_code' => $this->offer_code,
            'online_shop_link' => $this->online_shop_link,
            'expiry_date' => optional(Carbon::parse($this->expiry_date))->format(config('app.DATE_FORMAT')),

            'offer_type' => new OfferTypeResource($this->whenLoaded('offerType')),
            'clubs' => $this->whenLoaded('activeClubs', fn () => $this->clubs->pluck('uuid')),
            'gallery' => $this->whenLoaded(
                'gallery',
                fn () => $this->gallery->map(fn ($item) => file_url($item, 'name', 'medium'))
            ),
        ];
    }
}
