<?php

namespace App\Http\Resources\CRM\Lead;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lead\CrmComment */
/** @mixin \App\Models\Lead\CrmStep */
class HistoryableResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->resource->setRelations([])->toArray();
    }
}
