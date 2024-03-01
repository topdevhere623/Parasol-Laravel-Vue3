<?php

namespace App\Observers\Lead;

use App\Enum\CRM\History\ActionTypeEnum;
use App\Jobs\Nocrm\UpdateLeadNocrmJob;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadDuplicate;
use App\Notifications\Lead\LeadAssignmentNotification;
use App\Services\Crm\CrmHistoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LeadObserver
{
    public function saved(Lead $model)
    {
        if ($model->isDirty('email', 'phone') && ($model->email || $model->phone)) {
            $this->checkDuplicate($model);
        }

        if ($model->isDirty([
            'first_name',
            'last_name',
            'phone',
            'email',
            'status',
            'crm_step_id',
            'backoffice_user_id',
        ])
        ) {
            // UpdateLeadNocrmJob::dispatchUnless(request()->routeIs('nocrm-webhook'), $model);
        }
    }

    public function creating(Lead $model)
    {
        $model->created_by ??= auth()->id();
        $model->backoffice_user_id ??= auth()->id();
        $model->nocrm_owner_id ??= auth()->user()?->nocrm_id;
    }

    public function saving(Lead $model)
    {
        if ($model->isDirty('status')
            && !$model->isDirty('closed_at')
            && in_array($model->status, Lead::CLOSED_STATUSES)) {
            $model->closed_at = now();
        }
    }

    public function deleted(Lead $model)
    {
        LeadDuplicate::where('lead_id', $model->id)
            ->orWhere('duplicate_lead_id', $model->id)
            ->delete();
    }

    private function checkDuplicate(Lead $model): void
    {
        $phoneCheck = Str::of($model->phone);

        Lead::where('id', '!=', $model->id)
            ->where('created_at', '<', $model->created_at)
            ->where(
                fn (Builder $query) => $query->when(
                    $model->email,
                    fn (Builder $query) => $query->where('email', $model->email)
                )
                    ->when(
                        $model->phone,
                        fn (Builder $query) => $query
                            ->orWhere('phone', $model->phone)
                            ->orWhere('phone', 'LIKE', "%{$model->phone}")
                            ->when(
                                $phoneCheck->startsWith('971'),
                                fn (Builder $query) => $query->orWhere('phone', $phoneCheck->after('971')->toString())
                            )
                    )
            )
            ->latest()
            ->each(function (Lead $lead) use ($model) {
                LeadDuplicate::withTrashed()->firstOrCreate(
                    ['lead_id' => $lead->id, 'duplicate_lead_id' => $model->id]
                );
            });
    }

    public function created(Lead $model): void
    {
        CrmHistoryService::createHistory($model, ActionTypeEnum::LeadCreated);
        optional($model->backofficeUser)->notify(new LeadAssignmentNotification($model));
    }

    public function updated(Lead $model): void
    {
        if ($model->isDirty('status')) {
            CrmHistoryService::createHistory($model, ActionTypeEnum::StatusChanged);
        }

        if ($model->isDirty('crm_step_id')) {
            CrmHistoryService::createHistory($model, ActionTypeEnum::StepChanged);
        }

        if ($model->isDirty('backoffice_user_id')) {
            CrmHistoryService::createHistory($model, ActionTypeEnum::UserAssigned);
            optional($model->backofficeUser)->notify(new LeadAssignmentNotification($model));
        }

        if (
            request()->has('additional')
            && (
                Arr::get(request()->get('additional'), 'activity_id')
                || Arr::get(request()->get('additional'), 'comment_todo')
            )
        ) {
            $model->crmComments()->create([
                'content' => Arr::get(request()->get('additional'), 'comment_todo', ''),
                'crm_activity_id' => Arr::get(request()->get('additional'), 'activity_id') ?? null,
            ]);
        }
    }

    public function updating(Lead $model)
    {
        $newValuesForHistory = Arr::only($model->getDirty(), ['first_name', 'last_name', 'email', 'phone', 'notes']);
        $oldValuesForHistory = Arr::only($model->getOriginal(), array_keys($newValuesForHistory));
        if (!empty($newValuesForHistory)) {
            CrmHistoryService::createHistory(
                $model,
                ActionTypeEnum::LeadEdited,
                $newValuesForHistory,
                $oldValuesForHistory
            );
        }
    }

    public function pivotSynced(Lead $model, $relationName, $changes)
    {
        if ($relationName == 'leadTags' && (!empty($changes['attached']) || !empty($changes['detached']))) {
            // UpdateLeadNocrmJob::dispatchUnless(request()->routeIs('nocrm-webhook'), $model);
        }
    }
}
