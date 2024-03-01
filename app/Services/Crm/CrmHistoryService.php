<?php

namespace App\Services\Crm;

use App\Enum\CRM\History\ActionTypeEnum;
use App\Models\Lead\CrmComment;
use App\Models\Lead\CrmHistory;
use App\Models\Lead\Lead;

class CrmHistoryService
{
    public static function createHistory(
        CrmComment|Lead $model,
        ActionTypeEnum $actionType,
        $newValue = null,
        $oldValue = null
    ) {
        $additionalInfo = [];
        if ($actionType === ActionTypeEnum::StatusChanged) {
            $additional = request()->get('additional');
            $additionalInfo = [
                'cost' => $additional['cost'] ?? null,
                'comment_todo' => $additional['comment_todo'] ?? null,
                'comment_what_have' => $additional['comment_what_have'] ?? null,
                'activity_id' => $additional['activity_id'] ?? null,
            ];
        }

        $changes = match (true) {
            $actionType === ActionTypeEnum::StepChanged => [
                'name' => $model->crmStep->name,
                'position' => $model->crmStep->position,
            ],
            $actionType === ActionTypeEnum::StatusChanged => array_merge([
                'name' => $model->status,
            ], $additionalInfo),
            $actionType === ActionTypeEnum::UserAssigned => [
                'id' => $model->backofficeUser->id,
                'firstname' => $model->backofficeUser->first_name,
                'lastname' => $model->backofficeUser->last_name,
                'email' => $model->backofficeUser->email,
            ],
            $actionType === ActionTypeEnum::LeadEdited => [
                'new' => $newValue,
                'old' => $oldValue,
            ],
            default => null,
        };

        $leadId = match (get_class($model)) {
            Lead::class => $model->id,
            CrmComment::class => $model->commentable->id,
        };

        CrmHistory::create([
            'user_id' => auth()?->id(),
            'lead_id' => $leadId,
            'historyable_id' => $model->id,
            'historyable_type' => get_class($model),
            'action_type' => $actionType,
            'action_item' => $changes,
            'activity_id' => $additional['activity_id'] ?? null,
        ]);
    }
}
