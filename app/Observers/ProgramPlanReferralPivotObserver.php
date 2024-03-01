<?php

namespace App\Observers;

use App\Jobs\Coupon\UpdateProgramCouponsJob;
use App\Models\ProgramPlanReferralPivot;

class ProgramPlanReferralPivotObserver
{
    public function created(ProgramPlanReferralPivot $pivot)
    {
        UpdateProgramCouponsJob::dispatch($pivot->program_id, true);
    }

    public function deleted(ProgramPlanReferralPivot $pivot)
    {
        UpdateProgramCouponsJob::dispatch($pivot->program_id, true);
    }
}
