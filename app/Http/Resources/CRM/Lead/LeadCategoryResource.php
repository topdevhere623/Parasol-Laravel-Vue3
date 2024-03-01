<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lead\LeadTag */
class LeadCategoryResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'tags' => LeadTagResource::collection($this->whenLoaded('leadTags')),

        ];

        return $data;
    }
}
