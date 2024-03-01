<?php

namespace App\Observers;

use App\Models\Referral;

class ReferralObserver
{
    public function creating(Referral $model)
    {
        $model->uuid = \Str::orderedUuid()->toString();
    }
}
