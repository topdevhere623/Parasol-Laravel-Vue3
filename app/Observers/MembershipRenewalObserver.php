<?php

namespace App\Observers;

use App\Jobs\Plecto\PushMembershipRenewalPlectoJob;
use App\Models\Member\MembershipRenewal;

class MembershipRenewalObserver
{
    public function creating(MembershipRenewal $model): void
    {
        $model->token ??= \Str::random(100);
    }

    public function saved(MembershipRenewal $model): void
    {
        PushMembershipRenewalPlectoJob::dispatch($model);
    }
}
