<?php

namespace App\Http\Resources\v1\Program\Club;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Club\Club */
class ClubResource extends JsonResource
{
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
            'home_photo' => file_url($this->resource, 'home_photo', 'medium'),
            'logo' => file_url($this->resource, 'logo', 'medium'),
        ];
    }
}
