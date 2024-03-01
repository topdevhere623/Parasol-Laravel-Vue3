<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Country */
class CountryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'iso' => $this->iso,
            'country_name' => $this->country_name,
            'iso3' => $this->iso3,
            'phonecode' => $this->phonecode,
            'continent_code' => $this->continent_code,
            'continent_name' => $this->continent_name,
            'status' => $this->status,
            'active_cities_count' => $this->active_cities_count,
            'cities_count' => $this->cities_count,

            'activeCities' => CityResource::collection($this->whenLoaded('activeCities')),
            'cities' => CityResource::collection($this->whenLoaded('cities')),
        ];
    }
}
