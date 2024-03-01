<?php

namespace App\Http\Resources\CRM\LeadStatistics;

use App\Http\Resources\CRM\Lead\LeadBackofficeUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamPerformanceLeadStatisticsResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'backoffice_user' => LeadBackofficeUserResource::make($this->resource),
            'created_leads' => $this->created_leads ?? 0,
            'won_leads' => $this->won_leads ?? 0,
            'lost_cancelled_leads' => $this->lost_cancelled_leads ?? 0,
            'max_amount' => (int)$this->max_amount ?? 0,
            'total_amount' => (int)$this->total_amount ?? 0,

        ];
    }
}
