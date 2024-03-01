<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\backofficeUser */
class LeadBackofficeUserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'avatar' => file_url($this->resource, 'avatar', 'medium'),
        ];

        return array_merge($data);
    }
}
