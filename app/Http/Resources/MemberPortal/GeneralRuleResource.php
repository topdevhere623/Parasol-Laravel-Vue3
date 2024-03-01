<?php

namespace App\Http\Resources\MemberPortal;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\WebSite\GeneralRule */
class GeneralRuleResource extends JsonResource
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
            'image' => $this->image,
        ];

        return array_merge($data, $this->imageArray());
    }
}
