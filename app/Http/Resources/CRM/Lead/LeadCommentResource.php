<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lead\CrmComment */
class LeadCommentResource extends JsonResource
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
            'crm_activity_id' => $this->crm_activity_id,
            'is_pinned' => (bool)$this->is_pinned,
            'content' => $this->content,
            'raw_content' => $this->raw_content,
            'extended_info' => $this->extended_info,
            'created_at' => $this->created_at->format(config('app.DATETIME_FORMAT')),
            'crm_activity' => LeadActivityResource::make($this->whenLoaded('crmActivity')),
            'backoffice_user' => LeadBackofficeUserResource::make($this->whenLoaded('backofficeUser')),
            'attachments' => LeadAttachmentResource::collection($this->whenLoaded('crmAttachments')),
            'action_item' => LeadActionItemResource::make($this->whenLoaded('actionItem')),
        ];
    }
}
