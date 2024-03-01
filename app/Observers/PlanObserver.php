<?php

namespace App\Observers;

use App\Jobs\Member\SyncPlanClubsMemberJob;
use App\Models\Plan;

class PlanObserver
{
    public function pivotSynced(Plan $model, $relationName, $changes): void
    {
        if (str_contains($relationName, 'belongsToClubs')) {
            if (!empty($changes['attached']) || !empty($changes['detached']) || !empty($changes['updated'])) {
                // SyncPlanClubsMemberJob::dispatch($model);
            }
        }
    }
}
