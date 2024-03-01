<?php

namespace App\Http\Resources\CRM;

use App\Http\Resources\CityResource;
use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Club\Club */
class ClubSortingResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param  Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'sort' => $this->sort,

            'city' => new CityResource($this->whenLoaded('city')),
        ];

        return array_merge($data, $this->imageArray());
    }
}
