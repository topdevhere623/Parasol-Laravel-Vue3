<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lead\CrmEmail */
class LeadEmailResource extends JsonResource
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
            'to' => $this->to,
            'from' => $this->from,
            'from_name' => $this->from_name,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'subject' => $this->subject,
            'content' => $this->content,
            'threaded_content' => $this->threaded_content,
            'has_more_content' => $this->has_more_content,
            'is_read' => $this->is_read,
            'status' => $this->status,
            'lead_id' => $this->lead_id,
            'crm_comment_id' => $this->crm_comment_id,
            'backoffice_user_id' => $this->backoffice_user_id,
            'nocrm_id' => $this->nocrm_id,
            'nocrm_lead_id' => $this->nocrm_lead_id,
            'nocrm_owner_id' => $this->nocrm_owner_id,
            'attachments' => LeadAttachmentResource::collection($this->whenLoaded('crmAttachments')),
            'scheduled_at' => app_datetime_format($this->scheduled_at),
            'sent_at' => app_datetime_format($this->sent_at),
            'created_at' => app_datetime_format($this->created_at),
        ];
    }
}
