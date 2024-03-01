<?php

namespace App\Http\Resources\CRM\Lead;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lead\Lead */
class LeadKanbanResource extends JsonResource
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
            'title' => $this->title,
            'amount' => $this->amount ?? 0,
            'status' => $this->status,
            'crm_step' => $this->crmStep?->name,
            'crm_step_id' => $this->crm_step_id,
            'remind_date' => app_date_format($this->remind_date),
            'remind_time' => optional($this->remind_time)->format('H:i'),
            'reminder_at' => app_datetime_format($this->reminder_at),
            'reminder_time_ago' => $this->reminder_time_ago,
            'reminder_duration' => $this->reminder_duration,
            'reminder_activity_id' => $this->reminder_activity_id,
            'reminder_activity_log_id' => $this->reminder_activity_log_id,
            'reminder_note' => $this->reminder_note,
            'backoffice_user_id' => $this->backoffice_user_id,
            'backoffice_user' => LeadBackofficeUserResource::make($this->whenLoaded('backofficeUser')),
            'comments_count' => $this->crm_comments_count ?? 0,
            'created_at' => Carbon::parse($this->created_at)->format(config('app.DATETIME_FORMAT')),
        ];

        return array_merge($data);
    }
}
