<?php

namespace App\Http\Resources\CRM\Lead;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lead\Lead */
class LeadDuplicateResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'crm_step' => $this->step,
            'backoffice_user_id' => $this->backoffice_user_id,
            'backoffice_user' => LeadBackofficeUserResource::make($this->whenLoaded('backofficeUser')),
            'similarities' => $this->similarities ?? [],
            'created_at' => Carbon::parse($this->created_at)->format(config('app.DATETIME_FORMAT')),
        ];

        return array_merge($data);
    }
}
