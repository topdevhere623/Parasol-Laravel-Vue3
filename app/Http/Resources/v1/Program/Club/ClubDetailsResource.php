<?php

namespace App\Http\Resources\v1\Program\Club;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Club\Club */
class ClubDetailsResource extends JsonResource
{
    public static $wrap = 'data';

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'title_short' => $this->mc_display_name,
            'traffic' => $this->traffic,
            'city' => $this->city?->name,
            'slug' => $this->slug,
            'website' => $this->website,
            'description' => $this->description,
            'important_updates' => $this->important_updates,
            'guest_fees' => $this->guest_fees,
            'address' => $this->address,
            'check_in_area' => $this->check_in_area,
            'booking_policy_for_activities' => $this->booking_policy_for_activities,
            'parking' => $this->parking,
            'gmap_link' => $this->gmap_link,
            'contact' => $this->contact,
            'opening_hours_notes' => $this->opening_hours_notes,
            'club_overview' => $this->club_overview,
            'member_portal_url' => \URL::member('my-clubs/'.$this->slug),

            'home_photo' => file_url($this->resource, 'home_photo', 'medium'),
            'logo' => file_url($this->resource, 'logo', 'medium'),
            'detailed_club_info' => $this->when(
                $this->detailed_club_info,
                file_url($this->resource, 'detailed_club_info', 'original')
            ),

            'gallery' => $this->whenLoaded(
                'gallery',
                fn () => $this->gallery->map(fn ($item) => file_url($item, 'name', 'medium'))
            ),
        ];
    }
}
