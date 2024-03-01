<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadActionItemResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'type' => 'email',
            'email' => LeadEmailResource::make($this),
        ];
    }
}
