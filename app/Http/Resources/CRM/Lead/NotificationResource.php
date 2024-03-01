<?php

namespace App\Http\Resources\CRM\Lead;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'type' => $this->type,
            'notifiable' => [
                'notifiable_type' => $this->notifiable_type,
                'notifiable_id' => $this->notifiable_id,
            ],
            'data' => $this->data,
            'read_at' => app_date_format($this->read_at),
            'created_at' => app_date_format($this->created_at),
            'updated_at' => app_date_format($this->updated_at),
            'created_time_ago' => optional($this->created_at)->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE, short: true),
        ];
    }
}
