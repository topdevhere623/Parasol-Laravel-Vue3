<?php

namespace App\Http\Resources\MemberPortal;

use App\Http\Resources\GalleryResource;
use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Club\Club */
class ClubResource extends JsonResource
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
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'mc_display_name' => $this->mc_display_name,
            'traffic' => ucfirst($this->traffic),
            'traffic_is_available' => $this->traffic_is_available,
            'access_type' => $this->access_type,
            'home_photo' => $this->home_photo,
            'logo' => $this->logo,
            'website' => $this->website,
            'description' => $this->description,
            'important_updates' => $this->important_updates,
            'guest_fees' => $this->guest_fees,
            'detailed_club_info' => $this->detailed_club_info,
            'address' => $this->address,
            'checkout_photo' => $this->checkout_photo,
            'city' => optional($this->city)->name,
            'check_in_area' => $this->check_in_area,
            'booking_policy_for_activities' => $this->booking_policy_for_activities,
            'parking' => $this->parking,
            'gmap_link' => $this->gmap_link,
            'contact' => $this->contact,
            'opening_hours_notes' => $this->opening_hours_notes,
            'club_overview' => $this->club_overview,
            'favorite' => !!$this->is_favorite,
            'offers' => ClubOfferResource::collection($this->whenLoaded('activeOffers')),
            'gallery' => GalleryResource::collection($this->whenLoaded('gallery')),
        ];
        return array_merge($data, $this->imageArray());
    }
}
