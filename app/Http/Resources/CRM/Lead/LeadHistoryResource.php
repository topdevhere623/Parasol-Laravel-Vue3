<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lead\CrmHistory */
class LeadHistoryResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'action_type' => $this->action_type,
            'action_item' => $this->action_item,
            'user_id' => $this->user_id,
            'user_name' => $this->backofficeUser?->full_name ?? 'System',
            'created_at' => $this->created_at->format(config('app.DATETIME_FORMAT')),
            'history_object' => HistoryableResource::make($this->whenLoaded('historyable')),
            'crm_activity' => LeadActivityResource::make($this->whenLoaded('crmActivity')),
        ];
    }
}
