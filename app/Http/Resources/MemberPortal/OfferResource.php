<?php

namespace App\Http\Resources\MemberPortal;

use App\Traits\DynamicImageResourceTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Offer */
class OfferResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'offer_value' => $this->offer_value,
            'about' => $this->about,
            'terms' => $this->terms,
            'location' => $this->location,
            'area' => $this->area,
            'emirate' => $this->emirate,
            'website' => $this->website,
            'map' => $this->map,
            'offer_code' => $this->offer_code,
            'online_shop_link' => $this->online_shop_link,
            'expiry' => optional(Carbon::parse($this->expiry_date))->format(config('app.DATE_FORMAT')),

            'offer_type_id' => $this->offer_type_id,

            'clubs' => ClubResource::collection($this->whenLoaded('activeClubs')),
            'gallery' => GalleryResource::collection($this->whenLoaded('gallery')),
            'offerType' => new OfferTypeResource($this->whenLoaded('offerType')),
        ];

        return array_merge($data, $this->imageArray());
    }
}
