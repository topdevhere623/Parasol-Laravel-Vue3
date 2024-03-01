<?php

namespace App\Observers;

use App\Jobs\Passkit\PasskitUpdateMember;
use App\Jobs\ProgramApi\ProgramApiSendMemberJob;
use App\Models\Member\MemberClubPivot;

class MemberClubPivotObserver
{
    public function saved(MemberClubPivot $memberClubPivot): void
    {
        PasskitUpdateMember::dispatch($memberClubPivot->member_id)->delay(now()->addSeconds(5));
        ProgramApiSendMemberJob::dispatch($memberClubPivot->member_id)->delay(now()->addSeconds(3));
    }

    public function deleted(MemberClubPivot $memberClubPivot): void
    {
        $this->saved($memberClubPivot);
    }
}
