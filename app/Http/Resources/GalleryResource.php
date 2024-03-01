<?php

namespace App\Http\Resources;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Gallery */
class GalleryResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'name' => $this->name,
        ];

        return array_merge($data, $this->imageArray());
    }
}
