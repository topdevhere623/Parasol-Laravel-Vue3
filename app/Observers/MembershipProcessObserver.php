<?php

namespace App\Observers;

use App\Models\Member\MembershipProcess;

class MembershipProcessObserver
{
    public function saving(MembershipProcess $membershipProcess)
    {
        if (
            $membershipProcess->action_due_date
            && $membershipProcess->action_due_date->endOfDay()->isPast()
            && $membershipProcess->status == MembershipProcess::STATUSES['pending']
        ) {
            $membershipProcess->status = MembershipProcess::STATUSES['overdue'];
        }
    }
}
