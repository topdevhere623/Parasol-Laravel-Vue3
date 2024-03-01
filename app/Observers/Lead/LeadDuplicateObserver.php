<?php

namespace App\Observers\Lead;

use App\Models\Lead\LeadDuplicate;
use App\Services\NocrmService;

class LeadDuplicateObserver
{
    public function saved(LeadDuplicate $model)
    {
        if ($model->duplicateLead->nocrm_id) {
            $nocrmService = app(NocrmService::class);
            $remoteLead = $nocrmService->getLead($model->duplicateLead->nocrm_id);
            $data['tags'] = array_merge(['Duplicate'], $remoteLead['tags'] ?? []);
            $nocrmService->updateLead($model->duplicateLead->nocrm_id, $data);
        }
    }

    public function deleted(LeadDuplicate $model)
    {
        if ($model->duplicateLead->nocrm_id) {
            $nocrmService = app(NocrmService::class);
            $remoteLead = $nocrmService->getLead($model->duplicateLead->nocrm_id);
            $data['tags'] = array_diff($remoteLead['tags'] ?? [], ['Duplicate']);
            $nocrmService->updateLead($model->duplicateLead->nocrm_id, $data);
        }
    }
}
